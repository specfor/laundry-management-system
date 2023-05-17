<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width">
  <title>Payments</title>
  <script src="/assets/js/payments.js"></script>
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
    <h1 class="h1 text-white">Payments</h1>
    <br>
    <div class="row mt-3">
      <div class="col-md-6">
        <div class="input-group mb-3">
          <input id="orderIdInput" type="number" class="form-control" placeholder="Order Id" aria-label="" aria-describedby="">
          <button class="btn btn-dark fw-bold" type="button" id="clear">clear</button>
        </div>
      </div>

    </div>
    <table class="table table-dark table-striped mt-1">
      <thead>
        <tr>
          <td>Payment Id</td>
          <td>Order Id</td>
          <td>Amount</td>
          <td>Payment Date</td>
        </tr>
      </thead>
      <tbody id="paymentsTable">
      </tbody>
    </table>
  </div>

</body>

</html>