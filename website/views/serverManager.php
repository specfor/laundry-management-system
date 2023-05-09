<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width">
    <title>Server Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
            crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
            crossorigin="anonymous"></script>
</head>
<body class="bg-dark text-white ">
<div class="container-fluid ">
    <div class="row">
        <div class="col-10 mt-5 ">
            <div class="container">
                <h4 class="mb-3">Available Database Migrations</h4>

                <table class="table table-dark table-striped table-hover">
                    <thead class="table-secondary">
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Migration Name</th>
                        <th scope="col">Actions</th>
                    </tr>
                    </thead>
                    <tbody id="availableMigrationsTable">
                    </tbody>
                </table>

                <h4 class="mb-3 mt-5">Applied Database Migrations</h4>

                <table class="table table-dark table-striped table-hover">
                    <thead class="table-secondary">
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Migration Name</th>
                        <th scope="col">Applied Time</th>
                        <th scope="col">Success</th>
                    </tr>
                    </thead>
                    <tbody id="appliedMigrationsTable">
                    </tbody>
                </table>
            </div>
        </div>
        <div class="col-2 vh-100 border-start position-fixed top-0 end-0 overflow-auto">
            <h6 class="mt-5 text-center">API Maintenance Mode</h6>
            <button id="apiMaintenanceModeStatus" class="text-center btn btn-success w-100 fw-bold">Disabled</button>
            <h6 class="mt-3 text-center">Web Maintenance Mode</h6>
            <button id="webMaintenanceModeStatus" class="text-center btn btn-success w-100 fw-bold">Disabled</button>

            <h5 class="text-center mt-5 mb-3">Special Actions</h5>

            <h5 class="text-center mt-5 mb-3">Other Dashboards</h5>
            <a href="/server-performance" target="_blank" rel="noopener noreferrer"
               class="btn btn-secondary w-100 fw-bold">Realtime Statistics</a>
        </div>
    </div>
</div>

<script>
    const apiBaseUrl = "http://laundry-api.localhost/api/v1"
    const webBaseUrl = "http://laundry-web.localhost/"

    let migrationAuthToken = null
    let isApiInMaintenanceMode = null
    let isWebInMaintenanceMode = null

    checkMigrationToken()
    getMaintenanceMode('api')
    // getMaintenanceMode('web')
    getMigrations()
    getAppliedMigrations()

    setTimeout(() => {
        getMaintenanceMode('api')
        // getMaintenanceMode('web')
    }, 5000)

    let appliedMigrationsTable = document.getElementById("appliedMigrationsTable")
    let availableMigrationsTable = document.getElementById("availableMigrationsTable")

    let apiMaintenanceModeBtn = document.getElementById('apiMaintenanceModeStatus')
    let webMaintenanceModeBtn = document.getElementById('webMaintenanceModeStatus')

    apiMaintenanceModeBtn.addEventListener('click', () => {
        let msg = null
        if (isApiInMaintenanceMode)
            msg = 'disabling'
        else
            msg = 'enabling'
        if (confirm('Are you sure about ' + msg + ' API maintenance mode?'))
            setMaintenanceMode('api', !isApiInMaintenanceMode)
    })

    // Ui Handling Functions
    function callRunMigration(event, force = false) {
        if (confirm("Are you sure?"))
            runMigration(event.target.name, force)
    }

    function setUiMaintenanceMode(target, enabled = true) {
        let btn = null
        if (target === 'web') {
            btn = webMaintenanceModeBtn
            isWebInMaintenanceMode = enabled
        } else if (target === 'api') {
            btn = apiMaintenanceModeBtn
            isApiInMaintenanceMode = enabled
        }
        if (enabled) {
            btn.classList.remove('btn-success')
            btn.classList.add('btn-warning')
            btn.innerText = "Enabled"
        } else {
            btn.classList.remove('btn-warning')
            btn.classList.add('btn-success')
            btn.innerText = "Disabled"
        }
    }

    // Server Communication functions
    async function runMigration(name, force = false) {
        let payload = {
            'migration-name': name,
            'force-run': force
        }
        let response = await sendJsonPostRequest(apiBaseUrl + '/server-manager/migrations/run', payload)
        if (response.status === 200) {
            let data = await response.json()
            alert(data.body.message)
        }
        getAppliedMigrations()
    }

    async function getMigrations() {
        let response = await sendGetRequest(apiBaseUrl + '/server-manager/migrations')
        if (response.status === 200) {
            let data = await response.json()
            availableMigrationsTable.innerHTML = ''
            let i = 1
            for (const migration of data.body['available-migrations']) {
                availableMigrationsTable.innerHTML +=
                    '<tr><th scope="row">' + i + '</th><td>' + migration + '</td><td>' + '<button class="btn btn-primary btn-sm fw-bold"' +
                    'name="' + migration + '" onclick="callRunMigration(event)">' +
                    'RUN</button>' + '<button class="btn btn-danger btn-sm fw-bold ms-3" name="' + migration +
                    '" onclick="callRunMigration(event, true)">FORCE RUN</button>'
                i++
            }
        }
    }

    async function getAppliedMigrations() {
        let response = await sendGetRequest(apiBaseUrl + '/server-manager/migrations/applied')
        if (response.status === 200) {
            let data = await response.json()
            appliedMigrationsTable.innerHTML = ''
            for (const migration of data.body['applied-migrations']) {
                appliedMigrationsTable.innerHTML +=
                    '<tr><th scope="row">' + migration["id"] + '</th><td>' + migration['migration_name'] +
                    '</td><td>' + migration['time'] + '</td><td>' + (migration['status'] ? 'success' : 'failed') +
                    '</td></tr>'
            }
        }
    }

    async function setMaintenanceMode(target, enable = true) {
        let url = ''
        if (target === 'web') {
            url = webBaseUrl + ''
        } else if (target === 'api') {
            url = apiBaseUrl + '/server-manager/maintenanceMode'
        }
        let response = await sendJsonPostRequest(url, {enable: enable})
        if (response.status === 200) {
            let data = await response.json()
            if (data.statusMessage === 'success') {
                setUiMaintenanceMode(target, enable)
            }
        }
    }

    async function getMaintenanceMode(target) {
        let url = ''
        if (target === 'web') {
            url = webBaseUrl + ''
        } else if (target === 'api') {
            url = apiBaseUrl + '/server-manager/maintenanceMode'
        }
        let response = await sendGetRequest(url)
        if (response.status === 200) {
            let data = await response.json()
            if (data.statusMessage === 'success') {
                if (target === 'web')
                    isWebInMaintenanceMode = data.body['maintenance-mode']
                else if (target === 'api')
                    isApiInMaintenanceMode = data.body['maintenance-mode']
                setUiMaintenanceMode(target, data.body['maintenance-mode'])
            }
        }
    }

    async function checkMigrationToken() {
        if (localStorage.getItem('administrator-token')) {
            migrationAuthToken = localStorage.getItem('administrator-token')
            let response = await sendJsonPostRequest(apiBaseUrl + '/server-manager/migration-token/validate', {
                token: localStorage.getItem('administrator-token')
            })
            if (response.status === 200) {
                let data = await response.json()
                if (data.body.statusMessage === 'success') {
                    migrationAuthToken = localStorage.getItem('administrator-token')
                }
            }
        }
        let response = await sendGetRequest(apiBaseUrl + '/server-manager/migration-token')
        if (response.status === 200) {
            let data = await response.json()
            if (data.statusMessage === 'success') {
                migrationAuthToken = data.body.token
                localStorage.setItem('administrator-token', data.body.token)
            } else {
                alert(data.body.message)
            }
        }
    }

    async function sendJsonPostRequest(url, jsonBody) {
        return await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + localStorage.getItem('authToken'),
                'X-Administrator-Token': migrationAuthToken
            },
            body: JSON.stringify(jsonBody),
            credentials: "same-origin"
        })
    }

    async function sendGetRequest(url) {
        return await fetch(url, {
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('authToken'),
                'X-Administrator-Token': migrationAuthToken
            },
            credentials: "same-origin"
        })
    }
</script>
</body>
</html>