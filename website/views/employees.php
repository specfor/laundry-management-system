<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width">
  <title>Employees</title>
  <script src="/assets/js/employees.js"></script>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
    crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
    crossorigin="anonymous"></script>


</head>




<div class="container">
  <div class="container">
    <div class=" modal fade" id="editEmployee" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">Edit Employee</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
          </div>
          <div class="row text-center  ps-4 pe-4">
            <div class="input-group mb-3">
              <span class="input-group-text" id="basic-addon1">Name</span>
              <input type="text" class="form-control" aria-describedby="basic-addon1" id="efName"
                placeholder="Full Name">
            </div>
          
            <div class="input-group mb-3">
              <span class="input-group-text" id="basic-addon2">Contact No.</span>
              <input type="number" class="form-control " aria-describedby="basic-addon2" id="econtactNo"
                placeholder="*Contact No.">
            </div>
            <div class="input-group mb-3">
              <span class="input-group-text" id="basic-addon2">Address.</span>
              <textarea class="form-control" id="eaddress"></textarea>
            </div>
            <div class="input-group mb-3">
              <span class="input-group-text" id="basic-addon2">Date Joined</span>
              <input type="date" class="form-control " aria-describedby="basic-addon2" id="edateJoined">
            </div>

          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="button" class="btn btn-primary" id="btnEditEmp">Save Changes</button>
          </div>
        </div>
      </div>
    </div>



    <!-- This modal pop up when clicked on add new employee-->


    <div class="container">
      <div class="container">
        <div class=" modal fade" id="addEmployee" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Add New Employee</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
              </div>
              <div class="row text-center  ps-4 pe-4">
                <div class="input-group mb-3">
                  <span class="input-group-text" id="basic-addon1">Name</span>
                  <input type="text" class="form-control" aria-describedby="basic-addon1" id="fName"
                    placeholder="Full Name">
                </div>
                     
                <div class="input-group mb-3">
                  <span class="input-group-text" id="basic-addon2">Contact No.</span>
                  <input type="number" class="form-control " aria-describedby="basic-addon2" id="contactNo"
                    placeholder="*Contact No.">
                </div>
                <div class="input-group mb-3">
                  <span class="input-group-text" id="basic-addon2">Address.</span>
                  <textarea class="form-control" id="address"></textarea>
                </div>
                <div class="input-group mb-3">
                  <span class="input-group-text" id="basic-addon2">Date Joined</span>
                  <input type="date" class="form-control " aria-describedby="basic-addon2" id="dateJoined">
                </div>

              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="btnAddEmp">Add Employee</button>
              </div>
            </div>
          </div>
        </div>

        <body class="bg-secondary">
          <div class="container-fluid">
            <h1 class="h1 text-white">Employee List</h1>
            <button class="btn btn-dark ms-5 mt-3" type="button" data-bs-toggle="modal"
              data-bs-target="#addEmployee">+</button>
            <br>
            <div class="row mt-3">
              <div class="col-md-6">
                <div class="input-group mb-3">
                  <input id="empNameSearch" type="text" class="form-control" placeholder="Employee Name" aria-label="emp Name"
                    aria-describedby="btnClearFilterProductName">
                  <button class="btn btn-dark fw-bold" type="button" id="empNameClear">clear</button>
                </div>
              </div>
              <div class="col-md-3">
                <div class="input-group mb-3">
                  <input id="phoneNumSearch" type="number" class="form-control" placeholder="Phone Number" aria-label=""
                    aria-describedby="">
                  <button class="btn btn-dark fw-bold" type="button" id="phoneNumClear">clear</button>
                </div>
              </div>
             
            </div>
            <table class="table table-dark table-striped mt-1">
              <thead>
                <tr>
                  <td>ID</td>
                  <td>Name</td>
                  <td>Contact Info.</td>
                  <td>Address</td>
                  <td>Date Joined</td>
                  <td>Action</td>
                </tr>
              </thead>
              <tbody id="empTable">
              </tbody>
            </table>
          </div>



          <div class="modal" tabindex="-1" id="confirmDelete" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Confirm Deletion</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h5>This action is irreversible. Are you sure you want to delete this employee?</h5>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button id="btnConfirmDeletion" type="button" data-bs-dismiss="modal" class="btn btn-primary save">Yes</button>
            </div>
        </div>
    </div>
</div> 
        </body>

</html>