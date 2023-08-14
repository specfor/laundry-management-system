#![deny(warnings)]
use std::io::Error;
use std::{convert::Infallible, sync::Arc};

use powershell_script;
use printers;
use serde::{Deserialize, Serialize};
use tokio::sync::Mutex;
use warp::{hyper::StatusCode, reply::with_status, Filter};

use std::fs;

use escposify::device::File;
use escposify::printer::Printer;
use local_ip_address::local_ip;

// struct ServerStatus {
//     printer_name: String,
// }

#[derive(Serialize, Clone)]
struct PrinterData {
    name: String,
    id: String,
    system_name: String,
}

#[derive(Serialize, Deserialize)]
struct PrinterListOptions {
    force: bool,
}

#[derive(Serialize, Deserialize)]
struct SharePrinterResponse {
    success: bool,
    try_manual: bool,
    message: String,
}

#[derive(Deserialize, Serialize)]
struct PrintBody {
    content: String,
}

#[derive(Deserialize, Serialize)]
struct PrintBodyBlock {
    content: String,

    #[serde(default = "font_default")]
    font: String,

    #[serde(default = "style_default")]
    style: String,

    #[serde(default = "alignment_default")]
    alignment: String,

    #[serde(default = "font_height_default")]
    font_height: usize,

    #[serde(default = "font_width_default")]
    font_width: usize,
}

fn font_default() -> String {
    "A".to_owned()
}

fn alignment_default() -> String {
    "LT".to_owned()
}

fn style_default() -> String {
    "NORMAL".to_owned()
}

fn font_width_default() -> usize {
    0
}

fn font_height_default() -> usize {
    0
}

#[derive(Serialize, Deserialize)]
struct PrintResponse {
    success: bool,
    ip_error: bool,
    message: String,
}

type Printers = Arc<Mutex<Option<Vec<PrinterData>>>>;
type CurrentPrinter = Arc<Mutex<Option<String>>>;

#[tokio::main]
async fn main() {
    let printers: Printers = Arc::new(Mutex::new(Some(get_system_printers())));
    let current_printer: CurrentPrinter = Arc::new(Mutex::new(None));

    let list_printers = warp::path!("printer-list")
        .and(warp::query::<PrinterListOptions>())
        .and(with_printers(printers.clone()))
        .and_then(get_printers_handler);

    let set_current_printer = warp::path!("set-printer" / String)
        .and(with_current_printer(current_printer.clone()))
        .and(with_printers(printers.clone()))
        .and_then(set_current_printer_handler);

    let print_blocks = warp::post()
        .and(warp::path("print-blocks"))
        .and(warp::body::json())
        .and_then(print_blocks_handler);

    let print = warp::post()
        .and(warp::path("print"))
        .and(warp::body::json())
        .and_then(print_handler);

    let health = warp::get().map(|| "Server is running");

    // GET / => Server status
    // GET /printer-list?force=bool => List of printers
    // GET /set-printer/printer-id => Tries to share and set the printer. Will tell you if failed.
    // GET /print => Will try to print. Will tell you if failed
    // GET /print-blocks => Accepts blocks of text, enables simple styling
    let routes = list_printers
        .or(set_current_printer)
        .or(print)
        .or(print_blocks)
        .or(health)
        .with(warp::cors().allow_any_origin());

    warp::serve(routes).run(([127, 0, 0, 1], 30000)).await;
}

fn with_printers(
    printers: Printers,
) -> impl Filter<Extract = (Printers,), Error = Infallible> + Clone {
    warp::any().map(move || printers.clone())
}

fn with_current_printer(
    current_printer: CurrentPrinter,
) -> impl Filter<Extract = (CurrentPrinter,), Error = Infallible> + Clone {
    warp::any().map(move || current_printer.clone())
}

async fn get_printers_handler(
    PrinterListOptions { force }: PrinterListOptions,
    printers_state: Printers,
) -> Result<impl warp::Reply, Infallible> {
    let mut printers_guard = printers_state.lock().await;

    match &mut *printers_guard {
        Some(printers) => {
            // Printers have already been fetched
            // Don't fetch unless forces

            if force {
                *printers = get_system_printers();
            }

            Ok(warp::reply::json(&printers))
        }
        None => {
            let printers = get_system_printers();

            *printers_guard = Some(printers.to_vec());

            Ok(warp::reply::json(&printers))
        }
    }
}

async fn set_current_printer_handler(
    printer_id: String,
    current_printer: CurrentPrinter,
    printer_state: Printers,
) -> Result<impl warp::Reply, Infallible> {
    // Attempt to share the printer
    let printers_guard = printer_state.lock().await;
    match &*printers_guard {
        Some(printers) => {
            // Printers have already been fetched

            let printer_option = printers
                .to_vec()
                .into_iter()
                .filter(|printer| printer.id == printer_id)
                .last();

            if let Some(printer) = printer_option {
                let share_printer_ps_script = format!(
                    "Set-Printer -Name \"{}\" -Shared $True -ShareName \"Bill Printer\"",
                    printer.system_name
                );

                match powershell_script::run(&share_printer_ps_script) {
                    Ok(output) => {
                        *(current_printer.lock().await) = Some(printer.id);

                        Ok(with_status(
                            warp::reply::json(&SharePrinterResponse {
                                message: output.to_string(),
                                try_manual: false,
                                success: true,
                            }),
                            StatusCode::CREATED,
                        ))
                    }
                    Err(e) => Ok(with_status(
                        warp::reply::json(&SharePrinterResponse {
                            message: e.to_string(),
                            try_manual: true,
                            success: false,
                        }),
                        StatusCode::INTERNAL_SERVER_ERROR,
                    )),
                }
            } else {
                Ok(with_status(
                    warp::reply::json(&SharePrinterResponse {
                        message: "Invalid Printer Name".to_owned(),
                        try_manual: false,
                        success: false,
                    }),
                    StatusCode::BAD_REQUEST,
                ))
            }
        }
        None => Ok(with_status(
            warp::reply::json(&SharePrinterResponse {
                message: "Try listing the printers first".to_owned(),
                try_manual: false,
                success: false,
            }),
            StatusCode::BAD_REQUEST,
        )),
    }
}

async fn print_blocks_handler(
    request_body: Vec<PrintBodyBlock>,
) -> Result<impl warp::Reply, Infallible> {
    let my_local_ip = local_ip();

    if let Ok(my_local_ip) = my_local_ip {
        // after you shares your printer, use the following code
        // the IP is the your computer's IP address
        // "Receipt Printer" is the shared name of your printer
        // (check the control panel -> devices and printer)
        let path = format!("//{}/Bill Printer", &my_local_ip);

        // create a file and send it to the printer's path
        // if the path is not available/error you should handle it here.
        if let Ok(file) = File::<fs::File>::from_path(&path) {
            // prepare the variable
            let mut printer = Printer::new(file, None, None);

            let mut do_print = |blocks: &Vec<PrintBodyBlock>| -> Result<(), Error> {
                for block in blocks {
                    printer
                        .chain_font(&block.font)?
                        .chain_align(&block.alignment)?
                        .chain_style(&block.style)?
                        .chain_size(block.font_width, block.font_height)?
                        .chain_text(&block.content)?;
                }

                printer.chain_feed(3)?.chain_cut(false)?.flush()
            };

            if let Err(err) = do_print(&request_body) {
                return Ok(with_status(
                    warp::reply::json(&PrintResponse {
                        message: err.to_string(),
                        ip_error: false,
                        success: false,
                    }),
                    StatusCode::INTERNAL_SERVER_ERROR,
                ));
            }

            Ok(with_status(
                warp::reply::json(&PrintResponse {
                    message: "Print success".to_owned(),
                    ip_error: false,
                    success: true,
                }),
                StatusCode::OK,
            ))
        } else {
            Ok(with_status(
                warp::reply::json(&PrintResponse {
                    message: format!("Failed to open {}", &path),
                    ip_error: false,
                    success: false,
                }),
                StatusCode::INTERNAL_SERVER_ERROR,
            ))
        }
    } else {
        Ok(with_status(
            warp::reply::json(&PrintResponse {
                message: "Failed to get local IP".to_owned(),
                ip_error: true,
                success: false,
            }),
            StatusCode::INTERNAL_SERVER_ERROR,
        ))
    }
}

async fn print_handler(request_body: PrintBody) -> Result<impl warp::Reply, Infallible> {
    let my_local_ip = local_ip();

    if let Ok(my_local_ip) = my_local_ip {
        // after you shares your printer, use the following code
        // the IP is the your computer's IP address
        // "Receipt Printer" is the shared name of your printer
        // (check the control panel -> devices and printer)
        let path = format!("//{}/Bill Printer", &my_local_ip);

        // create a file and send it to the printer's path
        // if the path is not available/error you should handle it here.
        if let Ok(file) = File::<fs::File>::from_path(&path) {
            // prepare the variable
            let mut printer = Printer::new(file, None, None);

            let mut do_print = || -> Result<(), Error> {
                printer
                    .chain_font("C")?
                    .chain_align("RT")?
                    .chain_text(&request_body.content)?
                    .chain_feed(3)?
                    .chain_cut(false)?
                    .flush()
            };

            if let Err(err) = do_print() {
                return Ok(with_status(
                    warp::reply::json(&PrintResponse {
                        message: err.to_string(),
                        ip_error: false,
                        success: false,
                    }),
                    StatusCode::INTERNAL_SERVER_ERROR,
                ));
            }

            Ok(with_status(
                warp::reply::json(&PrintResponse {
                    message: "Print success".to_owned(),
                    ip_error: false,
                    success: true,
                }),
                StatusCode::OK,
            ))
        } else {
            Ok(with_status(
                warp::reply::json(&PrintResponse {
                    message: format!("Failed to open {}", &path),
                    ip_error: false,
                    success: false,
                }),
                StatusCode::INTERNAL_SERVER_ERROR,
            ))
        }
    } else {
        Ok(with_status(
            warp::reply::json(&PrintResponse {
                message: "Failed to get local IP".to_owned(),
                ip_error: true,
                success: false,
            }),
            StatusCode::INTERNAL_SERVER_ERROR,
        ))
    }
}

fn get_system_printers() -> Vec<PrinterData> {
    return printers::get_printers()
        .into_iter()
        .map(|printer| PrinterData {
            id: printer.id,
            name: printer.name,
            system_name: printer.system_name,
        })
        .collect::<Vec<PrinterData>>();
}
