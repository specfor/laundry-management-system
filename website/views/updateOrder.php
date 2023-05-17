<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width">
  <title>Update Order</title>
  <script src="/assets/js/updateOrder.js"></script>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
    crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
    crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@tarekraafat/autocomplete.js@10.2.7/dist/autoComplete.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tarekraafat/autocomplete.js@10.2.7/dist/css/autoComplete.01.min.css">

</head>
<body class="bg-secondary"> 
<div class="container">
    <div class="container">
        <div class="modal fade" id="updateItem" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true"
             data-bs-theme="dark">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Update Order</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                    </div>
                    <div class="row text-center  ps-4 pe-4">
                        <div class="col">


                            <div class="input-group mb-3">
                                <span class="input-group-text" id="basic-addon1">Item</span>
                                <select class="form-select" aria-label="Select Company" id="itemId">
                                    
                                </select>
                            </div>
                            <div class="row text-center ">
                                <div class="input-group mb-3 c">
                                    <span class="input-group-text" id="basic-addon3">Quantity</span>
                                    <input type="number" class="form-control" aria-describedby="basic-addon3"
                                           id="quantity">
                                </div>
                            </div>
                            <div class="input-group mb-3">
                                <span class="input-group-text" id="basic-addon3">Action</span>
                                <div class="ms-4" id="actionsAll">

                                </div>
                            </div>
                            <div class="input-group mb-3">
                                <span class="input-group-text" id="basic-addon3">Defects</span>

                                <div class="ms-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="Stains" id="check">
                                        <label class="form-check-label" for="flexCheckDefault">
                                            Stains
                                        </label>
                                    </div class="container">
                                    <div class="form-check ps-4">
                                        <input class="form-check-input" type="checkbox" value="Burnt Marks" id="check">
                                        <label class="form-check-label" for="flexCheckChecked">
                                            Burnt Marks
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="Dyes Bleeded" id="check">
                                        <label class="form-check-label" for="flexCheckDefault">
                                            Dyes Bleeded
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="Colours Faded"
                                               id="check">
                                        <label class="form-check-label" for="flexCheckChecked">
                                            Colours Faded
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="Missing Buttons"
                                               id="check">
                                        <label class="form-check-label" for="flexCheckDefault">
                                            Missing Buttons
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="Torn or Damaged"
                                               id="check">
                                        <label class="form-check-label" for="flexCheckChecked">
                                            Torn or Damaged
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="None" id="check">
                                        <label class="form-check-label" for="flexCheckChecked">
                                            None
                                        </label>
                                    </div>
                                </div>
                                <div class="row text-center  ps-4 pe-4 mt-3">
                                    <div class="input-group mb-3">
                                        <span class="input-group-text" id="basic-addon3">Delivery Date</span>
                                        <input type="date" class="form-control" aria-describedby="basic-addon3"
                                               id="deliveryDate">
                                    </div>
                                </div>


                            </div>

                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" id="saveChanges">Save Changes</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<div class="container-fluid position-relative " id="addOrderDiv">
        <h1 class="h1 text-white ms-4 mt-4">Update Order</h1>
        <button class="btn btn-dark mt-2 ms-5" data-bs-toggle="modal"
              data-bs-target="#updateItem">+</button>
        <table class="table table-dark table-striped mt-2">
            <thead>
                <td>Id</td>
                <td>Item</td>
                <td>Quantity</td>
                <td>Action</td>
                <td>Defects</td>
                <td>Delivery Due Date</td>
                <td>Edit</td>
            </thead>
            <tbody id="updateOrderTable">

            </tbody>
        </table>
        <button class="btn btn-danger mt-4 position-absolute end-0 me-4" id="paymentProceed">Proceed to Payment</button>


    </div>

    <div class="container-fluid">
        <div class="container-fluid row">
        <div class="col-6">
        <div class="container mt-5 mb-5 ms-5 mx-5 bg-light d-none" style="border-radius:1.2rem;" id="checkoutDiv">
            <h1 class="h1 text-center">Checkout</h1>
            <label for="price" class="">Price</label>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Colours Faded" id="autoCal">
                <label class="form-check-label" for="flexCheckChecked">Auto Calculate</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Colours Faded" id="customP">
                <label class="form-check-label" for="flexCheckChecked">Custom Price</label>
            </div>
            <input type="text" class="form-control mt-2" id="customPrice" placeHolder="Enter a Custom Price.">
            <button class="btn btn-danger mt-2 mb-3" id="confirmCheckout">Pay</button>
        </div>
        </div>

        <div class="col-6">
            <div class="container mt-5 mb-5 ms-5 mx-5 bg-light d-none" style="border-radius:1.2rem;" id="methodDiv">
            <h1 class="h1 text-center">Pay</h1>
            <div class="container bg-info" style="margin:auto;border-radius:1.2rem;">
                <h3 class="h3 text-center" id="totalPrice"></h3>    
        </div>
        <button class="btn btn-danger mt-2 mb-3" id="addPayment">Done</button>
        </div>
        </div>
        
        
    </div>


</body>