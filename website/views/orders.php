<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width">
  <title>Orders</title>
  <script src="/assets/js/orders.js"></script>
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
    <h1 class="h1 mt-3 ms-2 text-white" >Orders</h1>
    <table class="table table-dark table-striped mt-2">
      <thead>
        <td>Order Id</td>
        <td>Customer Name</td>
        <td>Order Added Date</td>
        <td>Items</td>
        <td>Amount</td>
        <td>Actions</td>
        <td>Delivery due date</td>
        <td>Total Price</td>
        <td>Edit</td>
      </thead>
      <tbody id="allOrdersTable">

      </tbody>
    </table>
    </div>
</body>