<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Debug Units Loading</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Debug Units Loading</h1>
        
        <div class="row">
            <div class="col-md-6">
                <h3>Test AJAX Call</h3>
                <div class="mb-3">
                    <label for="propertyId" class="form-label">Property ID:</label>
                    <input type="number" class="form-control" id="propertyId" value="1">
                </div>
                <button class="btn btn-primary" onclick="testUnitsLoading()">Test Units Loading</button>
                
                <div id="result" class="mt-3"></div>
            </div>
            
            <div class="col-md-6">
                <h3>Test Original Endpoint</h3>
                <button class="btn btn-secondary" onclick="testOriginalEndpoint()">Test /agent/meters/get-units</button>
                
                <div id="originalResult" class="mt-3"></div>
            </div>
        </div>
    </div>

    <script>
        function testUnitsLoading() {
            const propertyId = document.getElementById('propertyId').value;
            const resultDiv = document.getElementById('result');
            
            resultDiv.innerHTML = '<div class="alert alert-info">Testing...</div>';
            
            fetch(`/agent/debug/units/${propertyId}`)
                .then(response => response.json())
                .then(data => {
                    resultDiv.innerHTML = `
                        <div class="alert alert-success">
                            <h5>Success!</h5>
                            <p>Property ID: ${data.property_id}</p>
                            <p>Units Count: ${data.units_count}</p>
                            <pre>${JSON.stringify(data.units, null, 2)}</pre>
                        </div>
                    `;
                })
                .catch(error => {
                    resultDiv.innerHTML = `
                        <div class="alert alert-danger">
                            <h5>Error!</h5>
                            <p>${error.message}</p>
                        </div>
                    `;
                });
        }
        
        function testOriginalEndpoint() {
            const propertyId = document.getElementById('propertyId').value;
            const resultDiv = document.getElementById('originalResult');
            
            resultDiv.innerHTML = '<div class="alert alert-info">Testing original endpoint...</div>';
            
            fetch(`/agent/meters/get-units?property_id=${propertyId}`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                credentials: 'same-origin'
            })
                .then(response => response.json())
                .then(data => {
                    resultDiv.innerHTML = `
                        <div class="alert alert-success">
                            <h5>Success!</h5>
                            <p>Units Count: ${data.units ? data.units.length : 0}</p>
                            <pre>${JSON.stringify(data, null, 2)}</pre>
                        </div>
                    `;
                })
                .catch(error => {
                    resultDiv.innerHTML = `
                        <div class="alert alert-danger">
                            <h5>Error!</h5>
                            <p>${error.message}</p>
                        </div>
                    `;
                });
        }
    </script>
</body>
</html>
