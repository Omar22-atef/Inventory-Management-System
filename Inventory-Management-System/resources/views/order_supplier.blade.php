<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reorder Request</title>
</head>
<body>
   <p>Dear {{ $supplier->name ?? 'Supplier' }},</p>

<p>We would like to place a reorder for the following product:</p>

<ul>
    <li><strong>Product:</strong> {{ $product->name }}</li>
    <li><strong>Current Quantity:</strong> {{ $currentQuantity }}</li>
    <li><strong>Reorder Threshold:</strong> {{ $product->reorder_threshold }}</li>
</ul>

<p>Please send us your quotation and expected delivery time.</p>

<p>Best regards,<br>
Inventra Admin</p>
</body>
</html>
