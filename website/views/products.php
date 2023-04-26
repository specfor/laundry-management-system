<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width">
  <title>Products</title>
  <script src="/../public/assets/js/products.js"></script>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
    crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
    crossorigin="anonymous"></script>
</head> 
<body class="bg-secondary">
    <div class="container-fluid">
        <h1 class="h1 text-white mt-4 ms-4">Products</h1>
        <button class="btn btn-dark ms-4" data-bs-toggle="modal"data-bs-target="#addNewProduct">+</button>
        <div class="row mt-3">
            <div class="col-sm-6">
                <div class="input-group mb-3">
                    <input id="" type="text" class="form-control" placeholder="Product Name" aria-label="" aria-describedby="">
                    <button class="btn btn-dark fw-bold" type="button" id="">clear</button>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="input-group mb-3">
                    <input id="" type="number" class="form-control" placeholder="Id" aria-label="" aria-describedby="">
                    <button class="btn btn-dark fw-bold" type="button" id="">clear</button>
                  </div>
            </div>
        </div>
        <table class="table table-dark table-striped mt-1">
            <thead>
              <tr>
                <td>ID</td>
                <td>Product Name</td>
                <td>Action</td>
                <td>Unit Price</td>
                <td>Change</td>
              </tr>
            </thead>
            <tbody id="itemInputTable">
            </tbody>
          </table>
    </div>

    <div class="container">
        <div class="container">
            <div class="modal fade" id="addNewProduct" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true"
                 data-bs-theme="dark">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">Add Item</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                        </div>
                        <div class="row text-center  ps-4 pe-4">
                            <div class="col">
    
    
                                <div class="input-group mb-3">
                                    <span class="input-group-text" id="basic-addon1">Item</span>
                                    <input type="text" class="form-control" aria-describedby="basic-addon3" id="newProduct">
                                </div>
                                <div class="row text-center ">
                                    <div class="input-group mb-3 c">
                                        <span class="input-group-text" id="basic-addon3">Action</span>
                                        <select class="form-select" aria-label="Select Company" id="newActionId">
                                            <option>Dry Cleaning</option>
                                            <option>Washing</option>
                                            <option>Pressing</option>
                                            <option>Pressing & Dry Cleaning</option>
                                            <option>Pressing & Washing</option>
                                            <option>Washing & Dry Cleaning</option>
                                            <option>Washing, Pressing & Dry Cleaning</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="input-group mb-3">
                                    <span class="input-group-text" id="basic-addon2">Unit Price</span>
                                    <input type="number" class="form-control" aria-describedby="basic-addon3" id="unitPrice"
                                           placeholder="LKR.">
                                </div>
    
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-primary" id="btnNewAddItem">Add Item</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>

    <div class="container">
        <div class="container">
            <div class="modal fade" id="EditaddNewProduct" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true"
                 data-bs-theme="dark">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">Add Item</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                        </div>
                        <div class="row text-center  ps-4 pe-4">
                            <div class="col">
    
    
                                <div class="input-group mb-3">
                                    <span class="input-group-text" id="basic-addon1">Item</span>
                                    <input type="text" class="form-control" aria-describedby="basic-addon3" id="EditnewProduct">
                                </div>
                                <div class="row text-center ">
                                    <div class="input-group mb-3 c">
                                        <span class="input-group-text" id="basic-addon3">Action</span>
                                        <select class="form-select" aria-label="Select Company" id="EditnewActionId">
                                            <option>Dry Cleaning</option>
                                            <option>Washing</option>
                                            <option>Pressing</option>
                                            <option>Pressing & Dry Cleaning</option>
                                            <option>Pressing & Washing</option>
                                            <option>Washing & Dry Cleaning</option>
                                            <option>Washing, Pressing & Dry Cleaning</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="input-group mb-3">
                                    <span class="input-group-text" id="basic-addon2">Unit Price</span>
                                    <input type="number" class="form-control" aria-describedby="basic-addon3" id="EditunitPrice"
                                           placeholder="LKR.">
                                </div>
    
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-primary" id="btnNewAddItem">Save Changes</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>    
</body>
</html>