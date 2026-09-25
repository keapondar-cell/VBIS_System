<!doctype html>
<html>
<head>
  <meta charset="utf-8" />
  <title>Inventory API Docs</title>
  <link rel="stylesheet" href="https://unpkg.com/swagger-ui-dist@4/swagger-ui.css" />
</head>
<body>
  <div id="swagger-ui"></div>
  <script src="https://unpkg.com/swagger-ui-dist@4/swagger-ui-bundle.js"></script>
  <script>
    const ui = SwaggerUIBundle({
      url: '/inventory/openapi.json',
      dom_id: '#swagger-ui'
    });
  </script>
  <div style="margin:16px">
    <a href="/openapi.yaml" target="_blank">Download OpenAPI YAML</a>
    <div style="margin-top:8px">
      <strong>Auth header example:</strong>
      <pre>Authorization: Bearer &lt;your-jwt-or-token&gt;</pre>
    </div>
  </div>
</body>
</html>
