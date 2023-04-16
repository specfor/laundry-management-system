
window.addEventListener("load", function() {
  document.getElementById("btnAddEmp").addEventListener("click", checkData)
})

function checkData() {
  let fName = document.getElementById("fName").value
  let lName = document.getElementById("lName").value
  let nic = document.getElementById("nic").value
  let contactNo = document.getElementById("contactNo").value
  let address = document.getElementById("address").value
  let dateJoined = document.getElementById("dateJoined").value

  if (fName == "" || lName == "" || nic == "" || contactNo == "" || dateJoined == "") {
    alert("All the required fields must be filled")
  } else {
    updateTheTable(fName, lName, nic, contactNo, address, dateJoined)
  }
}

function updateTheTable(fName, lName, nic, contactNo, address, dateJoined) {



  let tableEmployee = document.getElementById("empTable")

  let row = tableEmployee.insertRow(-1)

  row.insertCell(0).innerHTML = "1"
  row.insertCell(1).innerHTML = fName
  row.insertCell(2).innerHTML = lName
  row.insertCell(3).innerHTML = nic
  row.insertCell(4).innerHTML = contactNo
  row.insertCell(5).innerHTML = address
  row.insertCell(6).innerHTML = dateJoined
  row.insertCell(7).innerHTML = `<button class="btn btn-primary" type="button"data-bs-toggle="modal" data-bs-target="#editEmployee">Edit Details</button>
                <button class="btn btn-danger" type="button">Delete</button>`


}