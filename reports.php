<?php

$DB_HOST = "127.0.0.1";
$DB_NAME = "ecommerce_db";
$DB_USER = "root";
$DB_PASS = "";

$DBconnection = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME", $DB_USER, $DB_PASS);

// first report php

$customers = $DBconnection->query("SELECT Customer_ID, Customer_Name FROM Customer ORDER BY Customer_ID")
  ->fetchAll(PDO::FETCH_ASSOC);

$customerid = (int)($_POST["Customer_ID"] ?? 0);

$customer = null;
$customeraddress = null;
$orders = [];
$total = 0;

if ($_SERVER["REQUEST_METHOD"] === "POST" && $customerid > 0) {

  $query = $DBconnection->prepare("SELECT Customer_ID, Customer_Name, Customer_Phone, Customer_Email FROM Customer WHERE Customer_ID=?");
  $query->execute([$customerid]);
  $customer = $query->fetch(PDO::FETCH_ASSOC);

  $query = $DBconnection->prepare("
    SELECT Address_line_1, Address_line_2, City, `State`, Country, Postal_Code
    FROM Customer_Address
    WHERE Customer_ID=?
    ORDER BY Address_ID DESC
    LIMIT 1
  ");
  $query->execute([$customerid]);
  $customeraddress = $query->fetch(PDO::FETCH_ASSOC);

  $query = $DBconnection->prepare("
    SELECT o.Order_ID, o.Order_date, SUM(d.Ordered_Quantity*d.Price_per_Quantity) AS OrderTotal
    FROM Orders o
    JOIN OrderDetails d ON d.Order_ID=o.Order_ID
    WHERE o.Customer_ID=?
    GROUP BY o.Order_ID, o.Order_date
    ORDER BY o.Order_ID
  ");
  $query->execute([$customerid]);
  $orders = $query->fetchAll(PDO::FETCH_ASSOC);

  $query = $DBconnection->prepare("
    SELECT COALESCE(SUM(d.Ordered_Quantity*d.Price_per_Quantity),0)
    FROM Orders o
    JOIN OrderDetails d ON d.Order_ID=o.Order_ID
    WHERE o.Customer_ID=?
  ");
  $query->execute([$customerid]);
  $total = (float)$query->fetchColumn();
}

//second report php

$customersTwo = $DBconnection->query("SELECT Customer_ID, Customer_Name FROM Customer ORDER BY Customer_ID")
  ->fetchAll(PDO::FETCH_ASSOC);

$customeridTwo = (int)($_POST["Customer_ID_shipment"] ?? 0);

$customerTwo = null;
$shipments = [];
$totalshipping = 0;
$numshipments = 0;

if ($_SERVER["REQUEST_METHOD"] === "POST" && $customeridTwo > 0) {

  $query = $DBconnection->prepare("SELECT Customer_ID, Customer_Name, Customer_Phone, Customer_Email FROM Customer WHERE Customer_ID=?");
  $query->execute([$customeridTwo]);
  $customerTwo = $query->fetch(PDO::FETCH_ASSOC);

  $query = $DBconnection->prepare("
    SELECT s.Shipment_ID, s.Order_ID, s.Vendor_ID,
           v.Vendor_Name,
           s.Shipping_Date, s.Arrival_Date, s.Shipment_Amount
    FROM Shipment s
    JOIN Vendor v ON v.Vendor_ID = s.Vendor_ID
    WHERE s.Customer_ID=?
    ORDER BY s.Shipment_ID
  ");
  $query->execute([$customeridTwo]);
  $shipments = $query->fetchAll(PDO::FETCH_ASSOC);

  $query = $DBconnection->prepare("
    SELECT COALESCE(SUM(Shipment_Amount),0) AS TotalShipping,
           COUNT(*) AS NumShipments
    FROM Shipment
    WHERE Customer_ID=?
  ");
  $query->execute([$customeridTwo]);
  $tot = $query->fetch(PDO::FETCH_ASSOC);

  $totalshipping = (float)($tot["TotalShipping"] ?? 0);
  $numshipments = (int)($tot["NumShipments"] ?? 0);
}

//Third report php script

$orderslist = $DBconnection->query("
  SELECT o.Order_ID, o.Order_date, c.Customer_Name
  FROM Orders o
  JOIN Customer c ON c.Customer_ID=o.Customer_ID
  ORDER BY o.Order_ID
")->fetchAll(PDO::FETCH_ASSOC);

$orderid = (int)($_POST["Order_ID"] ?? 0);

$order = null;
$lines = [];
$ordtotal = 0;

if ($_SERVER["REQUEST_METHOD"] === "POST" && $orderid > 0) {

  $query = $DBconnection->prepare("
    SELECT o.Order_ID, o.Order_date, c.Customer_ID, c.Customer_Name
    FROM Orders o
    JOIN Customer c ON c.Customer_ID=o.Customer_ID
    WHERE o.Order_ID=?
  ");
  $query->execute([$orderid]);
  $order = $query->fetch(PDO::FETCH_ASSOC);

  $query = $DBconnection->prepare("
    SELECT p.Product_ID, p.Product_Description,
           v.Vendor_Name,
           d.Ordered_Quantity, d.Price_per_Quantity,
           (d.Ordered_Quantity*d.Price_per_Quantity) AS LineTotal
    FROM OrderDetails d
    JOIN Product p ON p.Product_ID=d.Product_ID
    JOIN Vendor v ON v.Vendor_ID=p.Vendor_ID
    WHERE d.Order_ID=?
    ORDER BY p.Product_ID
  ");
  $query->execute([$orderid]);
  $lines = $query->fetchAll(PDO::FETCH_ASSOC);

  $query = $DBconnection->prepare("
    SELECT COALESCE(SUM(Ordered_Quantity*Price_per_Quantity),0)
    FROM OrderDetails
    WHERE Order_ID=?
  ");
  $query->execute([$orderid]);
  $ordtotal = (float)$query->fetchColumn();
}

// fourth report

$categories = $DBconnection->query("SELECT Category_ID, Category_Name FROM Product_Category ORDER BY Category_ID")
  ->fetchAll(PDO::FETCH_ASSOC);

$categoryid = (int)($_POST["Category_ID"] ?? 0);

$category = null;
$products = [];
$countproducts = 0;

if ($_SERVER["REQUEST_METHOD"] === "POST" && $categoryid > 0) {

  $query = $DBconnection->prepare("SELECT Category_ID, Category_Name FROM Product_Category WHERE Category_ID=?");
  $query->execute([$categoryid]);
  $category = $query->fetch(PDO::FETCH_ASSOC);

  $query = $DBconnection->prepare("
    SELECT p.Product_ID, p.Product_Description, p.Standard_Price, v.Vendor_Name
    FROM Product_Assignment pa
    JOIN Product p ON p.Product_ID=pa.Product_ID
    JOIN Vendor v ON v.Vendor_ID=p.Vendor_ID
    WHERE pa.Category_ID=?
    ORDER BY p.Product_ID
  ");
  $query->execute([$categoryid]);
  $products = $query->fetchAll(PDO::FETCH_ASSOC);

  $query = $DBconnection->prepare("SELECT COUNT(*) FROM Product_Assignment WHERE Category_ID=?");
  $query->execute([$categoryid]);
  $countproducts = (int)$query->fetchColumn();
}

// fifth report

$vendors = $DBconnection->query("SELECT Vendor_ID, Vendor_Name FROM Vendor ORDER BY Vendor_ID")
  ->fetchAll(PDO::FETCH_ASSOC);

$vendorid = (int)($_POST["Vendor_ID"] ?? 0);

$vendor = null;
$address = null;
$sales = [];
$totalrevenue = 0;
$totalunits = 0;

if ($_SERVER["REQUEST_METHOD"] === "POST" && $vendorid > 0) {

  $query = $DBconnection->prepare("SELECT Vendor_ID, Vendor_Name, Vendor_Phone, Vendor_email FROM Vendor WHERE Vendor_ID=?");
  $query->execute([$vendorid]);
  $vendor = $query->fetch(PDO::FETCH_ASSOC);

  $query = $DBconnection->prepare("
    SELECT Address_line_1, Address_line_2, City, `State`, Country, Postal_Code
    FROM Vendor_Address
    WHERE Vendor_ID=?
    ORDER BY Address_ID DESC
    LIMIT 1
  ");
  $query->execute([$vendorid]);
  $address = $query->fetch(PDO::FETCH_ASSOC);

  $query = $DBconnection->prepare("
    SELECT p.Product_ID, p.Product_Description,
           SUM(d.Ordered_Quantity) AS UnitsSold,
           SUM(d.Ordered_Quantity*d.Price_per_Quantity) AS Revenue
    FROM Product p
    JOIN OrderDetails d ON d.Product_ID=p.Product_ID
    WHERE p.Vendor_ID=?
    GROUP BY p.Product_ID, p.Product_Description
    ORDER BY p.Product_ID
  ");
  $query->execute([$vendorid]);
  $sales = $query->fetchAll(PDO::FETCH_ASSOC);

  $query = $DBconnection->prepare("
    SELECT COALESCE(SUM(d.Ordered_Quantity*d.Price_per_Quantity),0) AS TotalRevenue,
           COALESCE(SUM(d.Ordered_Quantity),0) AS TotalUnits
    FROM Product p
    JOIN OrderDetails d ON d.Product_ID=p.Product_ID
    WHERE p.Vendor_ID=?
  ");
  $query->execute([$vendorid]);
  $tot = $query->fetch(PDO::FETCH_ASSOC);
  $totalrevenue = (float)($tot["TotalRevenue"] ?? 0);
  $totalunits = (int)($tot["TotalUnits"] ?? 0);
}

?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Reports</title>
</head>
<body>

<h1>Ecommerce Market Customer Report Retrieval</h1>

<h2>Customer Account Information Request</h2>

<form method="post">
  Select Customer:

  <select name="Customer_ID" required>
    <option value="">select a customer</option>
    <?php foreach ($customers as $row): ?>
      <option value="<?php echo (int)$row["Customer_ID"]; ?>"
        <?php if ($customerid === (int)$row["Customer_ID"]) echo "selected"; ?>>
        
        <?php echo (int)$row["Customer_ID"] . " - " . htmlspecialchars($row["Customer_Name"]); ?>
      </option>
    <?php endforeach; ?>
  </select>

  <button type="submit">Generate Report</button>
</form>

<?php if ($customer): ?>

  <h2 style="text-align:center;">Ecommerce Online MarketPlace</h2>
  <h3 style="text-align:center;">Customer Information</h3>

  <p><b>Name:</b> <?php echo htmlspecialchars($customer["Customer_Name"]); ?></p>
  <p><b>ID:</b> <?php echo (int)$customer["Customer_ID"]; ?></p>
  <p><b>Email:</b> <?php echo htmlspecialchars($customer["Customer_Email"]); ?></p>
  <p><b>Phone:</b> <?php echo htmlspecialchars($customer["Customer_Phone"]); ?></p>

  <p><b>Address:</b><br>
    <?php
      if ($customeraddress) {
        echo htmlspecialchars($customeraddress["Address_line_1"]) . "<br>";
        
        if (!empty($customeraddress["Address_line_2"])) echo htmlspecialchars($customeraddress["Address_line_2"]) . "<br>";
          echo htmlspecialchars($customeraddress["City"]) . ", " . htmlspecialchars($customeraddress["State"]) . "<br>";
          echo htmlspecialchars($customeraddress["Country"]) . " " . htmlspecialchars($customeraddress["Postal_Code"]);
      } else {
          echo "No address.";
      }
    ?>
  </p>

  <h3>Orders</h3>
  <table border="1" cellpadding="6" cellspacing="0">
    <tr>
      <th>Order ID</th>
      <th>Order Date</th>
      <th>Order Total</th>
    </tr>
    <?php foreach ($orders as $o): ?>
      <tr>
        <td><?php echo (int)$o["Order_ID"]; ?></td>
        <td><?php echo htmlspecialchars($o["Order_date"]); ?></td>
        <td><?php echo number_format((float)$o["OrderTotal"], 2); ?></td>
      </tr>
    <?php endforeach; ?>
  </table>

  <p><b>Total Spendings:</b> <?php echo number_format($total, 2); ?></p>

<?php endif; ?>

<hr>
<h1>Ecommerce Market Shipment Report Retrieval</h1>

<h2>Shipment Information Request</h2>

<form method="post">
  Select Customer:

  <select name="Customer_ID_shipment" required>
    <option value="">select a customer</option>
    <?php foreach ($customersTwo as $row): ?>
      <option value="<?php echo (int)$row["Customer_ID"]; ?>"
        <?php if ($customeridTwo === (int)$row["Customer_ID"]) echo "selected"; ?>>
        
        <?php echo (int)$row["Customer_ID"] . " - " . htmlspecialchars($row["Customer_Name"]); ?>
      </option>
    <?php endforeach; ?>
  </select>

  <button type="submit">Generate Report</button>
</form>

<?php if ($customerTwo): ?>

  <h2 style="text-align:center;">Ecommerce Online MarketPlace</h2>
  <h3 style="text-align:center;">Shipment Report (By Customer)</h3>

  <p><b>Name:</b> <?php echo htmlspecialchars($customerTwo["Customer_Name"]); ?></p>
  <p><b>ID:</b> <?php echo (int)$customerTwo["Customer_ID"]; ?></p>
  <p><b>Email:</b> <?php echo htmlspecialchars($customerTwo["Customer_Email"]); ?></p>
  <p><b>Phone:</b> <?php echo htmlspecialchars($customerTwo["Customer_Phone"]); ?></p>

  <h3>Shipments</h3>
  <table border="1" cellpadding="6" cellspacing="0">
    <tr>
      <th>Shipment ID</th>
      <th>Order ID</th>
      <th>Vendor</th>
      <th>Shipping Date</th>
      <th>Arrival Date</th>
      <th>Shipment Amount</th>
    </tr>
    <?php foreach ($shipments as $s): ?>
      <tr>
        <td><?php echo (int)$s["Shipment_ID"]; ?></td>
        <td><?php echo (int)$s["Order_ID"]; ?></td>
        <td><?php echo htmlspecialchars($s["Vendor_Name"]); ?></td>
        <td><?php echo htmlspecialchars($s["Shipping_Date"]); ?></td>
        <td><?php echo htmlspecialchars($s["Arrival_Date"]); ?></td>
        <td><?php echo number_format((float)$s["Shipment_Amount"], 2); ?></td>
      </tr>
    <?php endforeach; ?>
  </table>

  <p><b>No. of Shipments:</b> <?php echo (int)$numshipments; ?></p>
  <p><b>Total Cost:</b> <?php echo number_format($totalshipping, 2); ?></p>

<?php endif; ?>

<hr>
<h1>Ecommerce Market Order Details Retrieval</h1>

<h2>Order Details Report Request</h2>

<form method="post">
  Select Order:

  <select name="Order_ID" required>
    <option value="">select an order</option>
    <?php foreach ($orderslist as $row): ?>
      <option value="<?php echo (int)$row["Order_ID"]; ?>"
        <?php if ($orderid === (int)$row["Order_ID"]) echo "selected"; ?>>
        <?php echo (int)$row["Order_ID"] . " - " . htmlspecialchars($row["Customer_Name"]) . " (" . htmlspecialchars($row["Order_date"]) . ")"; ?>
      </option>
    <?php endforeach; ?>
  </select>

  <button type="submit">Generate Report</button>
</form>

<?php if ($order): ?>

  <h2 style="text-align:center;">Ecommerce Online MarketPlace</h2>
  <h3 style="text-align:center;">Order Details</h3>

  <p><b>Order ID:</b> <?php echo (int)$order["Order_ID"]; ?></p>
  <p><b>Order Date:</b> <?php echo htmlspecialchars($order["Order_date"]); ?></p>
  <p><b>Customer:</b> <?php echo htmlspecialchars($order["Customer_Name"]); ?> (<?php echo (int)$order["Customer_ID"]; ?>)</p>

  <h3>Items</h3>
  <table border="1" cellpadding="6" cellspacing="0">
    <tr>
      <th>Product ID</th>
      <th>Description</th>
      <th>Vendor</th>
      <th>Qty</th>
      <th>Price</th>
      <th>Line Total</th>
    </tr>
    <?php foreach ($lines as $l): ?>
      <tr>
        <td><?php echo (int)$l["Product_ID"]; ?></td>
        <td><?php echo htmlspecialchars($l["Product_Description"]); ?></td>
        <td><?php echo htmlspecialchars($l["Vendor_Name"]); ?></td>
        <td><?php echo (int)$l["Ordered_Quantity"]; ?></td>
        <td><?php echo number_format((float)$l["Price_per_Quantity"], 2); ?></td>
        <td><?php echo number_format((float)$l["LineTotal"], 2); ?></td>
      </tr>
    <?php endforeach; ?>
  </table>

  <p><b>Order Total:</b> <?php echo number_format($ordtotal, 2); ?></p>

<?php endif; ?>

<hr>
<h1>Ecommerce Market Product Category report</h1>

<h2>Products by Category Report Request</h2>

<form method="post">
  Select Category:

  <select name="Category_ID" required>
    <option value="">select a category</option>
    <?php foreach ($categories as $row): ?>
      <option value="<?php echo (int)$row["Category_ID"]; ?>"
        <?php if ($categoryid === (int)$row["Category_ID"]) echo "selected"; ?>>
        
        <?php echo (int)$row["Category_ID"] . " - " . htmlspecialchars($row["Category_Name"]); ?>
      </option>
    <?php endforeach; ?>
  </select>

  <button type="submit">Generate Report</button>
</form>

<?php if ($category): ?>

  <h2 style="text-align:center;">Ecommerce Online MarketPlace</h2>
  <h3 style="text-align:center;">Products by Category</h3>

  <p><b>Category:</b> <?php echo htmlspecialchars($category["Category_Name"]); ?> (<?php echo (int)$category["Category_ID"]; ?>)</p>
  <p><b>Total No. of Products:</b> <?php echo (int)$countproducts; ?></p>

  <h3>Products</h3>
  <table border="1" cellpadding="6" cellspacing="0">
    <tr>
      <th>Product ID</th>
      <th>Description</th>
      <th>Vendor</th>
      <th>Standard Price</th>
    </tr>
    <?php foreach ($products as $p): ?>
      <tr>
        <td><?php echo (int)$p["Product_ID"]; ?></td>
        <td><?php echo htmlspecialchars($p["Product_Description"]); ?></td>
        <td><?php echo htmlspecialchars($p["Vendor_Name"]); ?></td>
        <td><?php echo number_format((float)$p["Standard_Price"], 2); ?></td>
      </tr>
    <?php endforeach; ?>
  </table>

<?php endif; ?>

<hr>
<h1>Ecommerce Market Vendor Report</h1>

<h2>Vendor Account Information Request</h2>

<form method="post">
  Select Vendor:

  <select name="Vendor_ID" required>
    <option value="">select a vendor</option>
    <?php foreach ($vendors as $row): ?>
      <option value="<?php echo (int)$row["Vendor_ID"]; ?>"
        <?php if ($vendorid === (int)$row["Vendor_ID"]) echo "selected"; ?>>
        
        <?php echo (int)$row["Vendor_ID"] . " - " . htmlspecialchars($row["Vendor_Name"]); ?>
      </option>
    <?php endforeach; ?>
  </select>

  <button type="submit">Generate Report</button>
</form>

<?php if ($vendor): ?>

  <h2 style="text-align:center;">Ecommerce Online MarketPlace</h2>
  <h3 style="text-align:center;">Vendor Information</h3>

  <p><b>Name:</b> <?php echo htmlspecialchars($vendor["Vendor_Name"]); ?></p>
  <p><b>ID:</b> <?php echo (int)$vendor["Vendor_ID"]; ?></p>
  <p><b>Email:</b> <?php echo htmlspecialchars($vendor["Vendor_email"]); ?></p>
  <p><b>Phone:</b> <?php echo htmlspecialchars($vendor["Vendor_Phone"]); ?></p>

  <p><b>Address:</b><br>
    <?php
      if ($address) {
        echo htmlspecialchars($address["Address_line_1"]) . "<br>";
        
        if (!empty($address["Address_line_2"])) echo htmlspecialchars($address["Address_line_2"]) . "<br>";
          echo htmlspecialchars($address["City"]) . ", " . htmlspecialchars($address["State"]) . "<br>";
          echo htmlspecialchars($address["Country"]) . " " . htmlspecialchars($address["Postal_Code"]);
      } else {
          echo "No address.";
      }
    ?>
  </p>

  <h3>Sales (by Product)</h3>
  <table border="1" cellpadding="6" cellspacing="0">
    <tr>
      <th>Product ID</th>
      <th>Product Description</th>
      <th>Units Sold</th>
      <th>Revenue</th>
    </tr>
    <?php foreach ($sales as $s): ?>
      <tr>
        <td><?php echo (int)$s["Product_ID"]; ?></td>
        <td><?php echo htmlspecialchars($s["Product_Description"]); ?></td>
        <td><?php echo (int)$s["UnitsSold"]; ?></td>
        <td><?php echo number_format((float)$s["Revenue"], 2); ?></td>
      </tr>
    <?php endforeach; ?>
  </table>

  <p><b>Units Sold:</b> <?php echo (int)$totalunits; ?></p>
  <p><b>Revenue:</b> <?php echo number_format($totalrevenue, 2); ?></p>

<?php endif; ?>


</body>
</html>

