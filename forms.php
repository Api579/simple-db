<?php

$DB_HOST = "127.0.0.1";
$DB_NAME = "ecommerce_db";
$DB_USER = "root";
$DB_PASS = "";


$DBconnection = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME", $DB_USER, $DB_PASS);

$msg = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

  if (($_POST["form_type"] ?? "") === "customer") {
    $DBconnection->prepare("INSERT INTO
      Customer (Customer_Name,Customer_Phone,Customer_Email)
      VALUES (?,?,?)")
        ->execute(
          [$_POST["Customer_Name"],
           $_POST["Customer_Phone"],
           $_POST["Customer_Email"]]
        );
    $idkey = $DBconnection->lastInsertId();

    $DBconnection->prepare("INSERT INTO
      Customer_Address (
      Customer_ID,
      Address_line_1,
      Address_line_2,
      City,
      `State`,
      Country,
      Postal_Code)
      VALUES (?,?,?,?,?,?,?)")
        ->execute(
          [$idkey,
           $_POST["Address_line_1"],
           (($_POST["Address_line_2"] ?? "") ?: null),
           $_POST["City"],
           $_POST["State"],
           $_POST["Country"],
           $_POST["Postal_Code"]]
        );
    $msg = "Customer saved ($idkey)";

  } elseif (($_POST["form_type"] ?? "") === "vendor") {
    $DBconnection->prepare("INSERT INTO
      Vendor (Vendor_Name,Vendor_Phone,Vendor_email)
      VALUES (?,?,?)")
        ->execute(
          [$_POST["Vendor_Name"],
           $_POST["Vendor_Phone"],
           $_POST["Vendor_email"]]
        );
    $idkey = $DBconnection->lastInsertId();

    $DBconnection->prepare("INSERT INTO
      Vendor_Address (
      Vendor_ID,
      Address_line_1,
      Address_line_2,
      City,
      `State` ,
      Country,
      Postal_Code)
      VALUES (?,?,?,?,?,?,?)")
        ->execute(
          [$idkey,
           $_POST["Address_line_1"],
           (($_POST["Address_line_2"] ?? "") ?: null),
           $_POST["City"],
           $_POST["State"],
           $_POST["Country"],
           $_POST["Postal_Code"]]
        );
    $msg = "Vendor saved ($idkey)";

  } elseif (($_POST["form_type"] ?? "") === "product") {
    $DBconnection->prepare("INSERT INTO
      Product (Product_Description, Standard_Price, Vendor_ID)
      VALUES (?,?,?)")
        ->execute(
          [$_POST["Product_Description"],
           $_POST["Standard_Price"],
           (int)$_POST["Vendor_ID"]]
        );
    $idkey = $DBconnection->lastInsertId();

    $DBconnection->prepare("INSERT INTO
      Product_Assignment (Product_ID, Category_ID)
      VALUES (?,?)")
        ->execute(
          [$idkey,
           (int)$_POST["Category_ID"]]
        );
    $msg = "Product saved ($idkey)";

  } elseif (($_POST["form_type"] ?? "") === "order") {
    $DBconnection->prepare("INSERT INTO
      `Orders` (Order_date, Customer_ID)
      VALUES (?,?)")
        ->execute(
          [$_POST["Order_date"],
           (int)$_POST["Customer_ID"]]
        );
    $idkey = $DBconnection->lastInsertId();

    $DBconnection->prepare("INSERT INTO
      OrderDetails (Order_ID, Product_ID, Ordered_Quantity, Price_per_Quantity)
      VALUES (?,?,?,?)")
        ->execute(
          [$idkey,
           (int)$_POST["Product_ID"],
           (int)$_POST["Ordered_Quantity"],
           $_POST["Price_per_Quantity"]]
        );
    $msg = "Order saved ($idkey)";

  } elseif (($_POST["form_type"] ?? "") === "shipment") {
    $DBconnection->prepare("INSERT INTO
      Shipment (Order_ID,
                Customer_ID,Vendor_ID,
                Shipping_Date,
                Arrival_Date,
                Shipment_Amount)
      VALUES (?,?,?,?,?,?)")
        ->execute(
          [(int)$_POST["Order_ID"],
           (int)$_POST["Customer_ID"],
           (int)$_POST["Vendor_ID"],
           $_POST["Shipping_Date"],
           $_POST["Arrival_Date"],
           $_POST["Shipment_Amount"]]
        );
    $idkey = $DBconnection->lastInsertId();
    $msg = "Shipment saved ($idkey)";
  }
}

?>

<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Data Entry Forms</title>
</head>
<body>
<h2>Data Entry Forms</h2>

<?php if ($msg)
  echo "<p><b>".htmlspecialchars($msg)."</b></p>";
?>

<hr>
<h3>Form 1: Add Customer </h3>
<form method="post">
  <input type="hidden" name="form_type" value="customer">
  Name <input name="Customer_Name" required><br><br>
  Phone <input name="Customer_Phone" required><br><br>
  Email <input name="Customer_Email" type="email" required><br><br>
  Address Line 1 <input name="Address_line_1" required><br><br>
  Address Line 2 (optional) <input name="Address_line_2"><br><br>
  City <input name="City" required><br><br>
  State <input name="State" required><br><br>
  Country <input name="Country" required><br><br>
  Postal Code <input name="Postal_Code" required><br><br>
  <button type="submit">submit</button>
</form>

<hr>
<h3>Form 2: Add Vendor </h3>
<form method="post">
  <input type="hidden" name="form_type" value="vendor">
  Name <input name="Vendor_Name" required><br><br>
  Phone <input name="Vendor_Phone" required><br><br>
  Email <input name="Vendor_email" type="email" required><br><br>
  Address Line 1 <input name="Address_line_1" required><br><br>
  Address Line 2 (optional) <input name="Address_line_2"><br><br>
  City <input name="City" required><br><br>
  State <input name="State" required><br><br>
  Country <input name="Country" required><br><br>
  Postal Code <input name="Postal_Code" required><br><br>
  <button type="submit">submit</button>
</form>

<hr>
<h3>Form 3: Add Product </h3>
<form method="post">
  <input type="hidden" name="form_type" value="product">
  Description <input name="Product_Description" required><br><br>
  Standard Price <input name="Standard_Price" required><br><br>
  Vendor_ID <input name="Vendor_ID" required><br><br>
  Category_ID <input name="Category_ID" required><br><br>
  <button type="submit">Submit</button>
</form>

<hr>
<h3>Form 4: Add Order </h3>
<form method="post">
  <input type="hidden" name="form_type" value="order">
  Order_date (YYYY-MM-DD) <input name="Order_date" required><br><br>
  Customer_ID <input name="Customer_ID" required><br><br>
  Product_ID <input name="Product_ID" required><br><br>
  Ordered_Quantity <input name="Ordered_Quantity" required><br><br>
  Price_per_Quantity <input name="Price_per_Quantity" required><br><br>
  <button type="submit">Submit</button>
</form>

<hr>
<h3>Form 5: Add Shipment</h3>
<form method="post">
  <input type="hidden" name="form_type" value="shipment">
  Order_ID <input name="Order_ID" required><br><br>
  Customer_ID <input name="Customer_ID" required><br><br>
  Vendor_ID <input name="Vendor_ID" required><br><br>
  Shipping_Date (YYYY-MM-DD) <input name="Shipping_Date" required><br><br>
  Arrival_Date (YYYY-MM-DD) <input name="Arrival_Date" required><br><br>
  Shipment_Amount <input name="Shipment_Amount" required><br><br>
  <button type="submit">Submit</button>
</form>

</body>
</html>