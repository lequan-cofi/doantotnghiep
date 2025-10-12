<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Test Units Loading</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Test Units Loading</h1>
        
        <div class="row">
            <div class="col-md-6">
                <h3>Test Routes</h3>
                <div class="mb-3">
                    <label for="propertyId" class="form-label">Property ID:</label>
                    <input type="number" class="form-control" id="propertyId" value="1">
                </div>
                
                <div class="d-grid gap-2">
                    <button class="btn btn-primary" onclick="testRoute('units-test')">Test Units Route</button>
                    <button class="btn btn-info" onclick="testRoute('data-test')">Test Data Route</button>
                    <button class="btn btn-secondary" onclick="testRoute('test-units')">Test Basic Route</button>
                    <button class="btn btn-warning" onclick="testRoute('meters/get-units')">Test Original Route</button>
                </div>
                
                <div id="result" class="mt-3"></div>
            </div>
            
            <div class="col-md-6">
                <h3>Debug Info</h3>
                <div class="alert alert-info">
                    <h6>Current URL:</h6>
                    <p id="currentUrl"></p>
                </div>
                
                <div class="alert alert-info">
                    <h6>CSRF Token:</h6>
                    <p id="csrfToken"></p>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('currentUrl').textContent = window.location.href;
            document.getElementById('csrfToken').textContent = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        });
        
        function testRoute(route) {
            const propertyId = document.getElementById('propertyId').value;
            const resultDiv = document.getElementById('result');
            
            resultDiv.innerHTML = '<div class="alert alert-info">Testing...</div>';
            
            const url = `/agent/${route}?property_id=${propertyId}`;
            
            fetch(url, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                credentials: 'same-origin'
            })
            .then(response => {
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                resultDiv.innerHTML = `
                    <div class="alert alert-success">
                        <h5>Success!</h5>
                        <p><strong>Route:</strong> ${route}</p>
                        <p><strong>URL:</strong> ${url}</p>
                        <p><strong>Units Count:</strong> ${data.units ? data.units.length : 0}</p>
                        <pre>${JSON.stringify(data, null, 2)}</pre>
                    </div>
                `;
            })
            .catch(error => {
                console.error('Error:', error);
                resultDiv.innerHTML = `
                    <div class="alert alert-danger">
                        <h5>Error!</h5>
                        <p><strong>Route:</strong> ${route}</p>
                        <p><strong>URL:</strong> ${url}</p>
                        <p><strong>Error:</strong> ${error.message}</p>
                    </div>
                `;
            });
        }
    </script>
</body>
</html>
