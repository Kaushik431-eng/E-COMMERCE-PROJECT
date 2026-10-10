<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>API Tester</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        .tester {
            max-width: 700px;
            margin: 30px auto;
            background: #fff;
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }

        .tester h3 {
            margin: 18px 0 10px;
        }

        .tester input {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
        }

        .tester .row {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        pre {
            background: #111827;
            color: #a7f3d0;
            padding: 14px;
            border-radius: 8px;
            margin-top: 14px;
            overflow: auto;
            max-height: 350px;
            min-height: 60px;
        }
    </style>
</head>

<body>
    <div class="tester">
        <h2>API Tester</h2>

        <h3>1. Login API</h3>
        <input type="email" id="email" placeholder="Email">
        <input type="password" id="password" placeholder="Password">
        <button class="btn" onclick="doLogin()">Login (POST)</button>

        <h3>2. Products API</h3>
        <div class="row">
            <button class="btn" onclick="callApi('products.php')">All Products</button>
            <button class="btn" onclick="callApi('products.php?id=1')">Product id 1</button>
            <button class="btn" onclick="callApi('products.php?search=mouse')">Search "mouse"</button>
        </div>

        <h3>3. Orders API (login zaroori)</h3>
        <div class="row">
            <button class="btn" onclick="callApi('orders.php')">My Orders</button>
        </div>

        <h3>Response</h3>
        <pre id="output">Yahan response dikhega...</pre>
    </div>

    <script>
        const out = document.getElementById("output");

        function show(data) {
            out.textContent = JSON.stringify(data, null, 2);
        }

        function callApi(path) {
            fetch(path)
                .then(r => r.json())
                .then(show)
                .catch(e => show({
                    error: String(e)
                }));
        }

        function doLogin() {
            fetch("login.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({
                        email: document.getElementById("email").value,
                        password: document.getElementById("password").value
                    })
                })
                .then(r => r.json())
                .then(show)
                .catch(e => show({
                    error: String(e)
                }));
        }
    </script>
</body>

</html>