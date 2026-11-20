CREATE DATABASE ecommerce_db;

USE ecommerce_db;

-- create 10 tables with data integrity applied, and 2x indexes

CREATE TABLE Vendor (
  Vendor_ID INT AUTO_INCREMENT PRIMARY KEY,
  Vendor_Name VARCHAR(200) NOT NULL,
  Vendor_Phone VARCHAR(20) NOT NULL,
  Vendor_email VARCHAR(200) NOT NULL
);

CREATE TABLE Customer (
  Customer_ID INT AUTO_INCREMENT PRIMARY KEY,
  Customer_Name VARCHAR(100) NOT NULL,
  Customer_Phone VARCHAR(20) NOT NULL,
  Customer_Email VARCHAR(200) NOT NULL,
  -- created indexes 1x, first unique index
  CONSTRAINT
    Unique_Customer_Email UNIQUE (Customer_Email)
);

CREATE TABLE Product_Category (
  Category_ID INT AUTO_INCREMENT PRIMARY KEY,
  Category_Name VARCHAR(100) NOT NULL
);

CREATE TABLE Product (
  Product_ID INT AUTO_INCREMENT PRIMARY KEY,
  Product_Description VARCHAR(200) NOT NULL,
  Standard_Price DECIMAL(10,2) NOT NULL,
  Vendor_ID INT NOT NULL,
  CONSTRAINT foreign_key_product_vendor
    FOREIGN KEY (Vendor_ID) REFERENCES Vendor(Vendor_ID),
  CONSTRAINT Product_Price_Range
    CHECK (Standard_Price > 0)
);

CREATE TABLE Orders (
  Order_ID INT AUTO_INCREMENT PRIMARY KEY,
  Order_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  Customer_ID INT NOT NULL,
  CONSTRAINT foreign_key_orders_customer
    FOREIGN KEY (Customer_ID) REFERENCES Customer(Customer_ID),
    -- created second index
    INDEX Index_Order_Date (Order_date)
);

CREATE TABLE OrderDetails (
  Order_ID INT NOT NULL,
  Product_ID INT NOT NULL,
  Ordered_Quantity INT NOT NULL DEFAULT 1,
  Price_per_Quantity DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (Order_ID, Product_ID),
  CONSTRAINT foreign_key_details_order
    FOREIGN KEY (Order_ID) REFERENCES Orders(Order_ID),
  CONSTRAINT foreign_key_od_product
    FOREIGN KEY (Product_ID) REFERENCES Product(Product_ID),
  Constraint Ordered_Quantity_Range
    CHECK (Ordered_Quantity >= 1),
  CONSTRAINT Price_per_Quantity_Range
    CHECK (Price_per_Quantity > 0)
);

CREATE TABLE Shipment (
  Shipment_ID INT AUTO_INCREMENT PRIMARY KEY,
  Order_ID INT NOT NULL,
  Customer_ID INT NOT NULL,
  Vendor_ID INT NOT NULL,
  Shipping_Date DATE NOT NULL,
  Arrival_Date DATE NOT NULL,
  Shipment_Amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  CONSTRAINT foreign_key_ship_order
    FOREIGN KEY (Order_ID) REFERENCES Orders(Order_ID),
  CONSTRAINT foreign_key_ship_customer
    FOREIGN KEY (Customer_ID) REFERENCES Customer(Customer_ID),
  CONSTRAINT foreign_key_ship_vendor
    FOREIGN KEY (Vendor_ID) REFERENCES Vendor(Vendor_ID),
  Constraint shup_amount_range
    Check (Shipment_Amount >= 0),
  Constraint check_ship_dates
    Check (Arrival_Date >= Shipping_Date)
);

CREATE TABLE Customer_Address (
  Address_ID INT AUTO_INCREMENT PRIMARY KEY,
  Customer_ID INT NOT NULL,
  Address_line_1 VARCHAR(150) NOT NULL,
  Address_line_2 VARCHAR(150),
  City VARCHAR(60) NOT NULL,
  State VARCHAR(60) NOT NULL,
  Country VARCHAR(60) NOT NULL,
  Postal_Code VARCHAR(20) NOT NULL,
  CONSTRAINT foreign_key_address_customer
    FOREIGN KEY (Customer_ID) REFERENCES Customer(Customer_ID)
);

CREATE TABLE Vendor_Address (
  Address_ID INT AUTO_INCREMENT PRIMARY KEY,
  Vendor_ID INT NOT NULL,
  Address_line_1 VARCHAR(150) NOT NULL,
  Address_line_2 VARCHAR(150),
  City VARCHAR(60) NOT NULL,
  State VARCHAR(60) NOT NULL,
  Country VARCHAR(60) NOT NULL,
  Postal_Code VARCHAR(20) NOT NULL,
  CONSTRAINT foreign_key_address_vendor
    FOREIGN KEY (Vendor_ID) REFERENCES Vendor(Vendor_ID)
);

CREATE TABLE Product_Assignment (
  Product_ID INT NOT NULL,
  Category_ID INT NOT NULL,
  PRIMARY KEY (Product_ID, Category_ID),
  CONSTRAINT foreign_key_assignment_product
    FOREIGN KEY (Product_ID) REFERENCES Product(Product_ID),
  CONSTRAINT foreign_key_assignment_category
    FOREIGN KEY (Category_ID) REFERENCES Product_Category(Category_ID)
);

-- 5 records per table 

INSERT INTO Vendor (Vendor_ID, Vendor_Name, Vendor_Phone, Vendor_email) VALUES
(1, 'Courts', '50000001', 'Courts@gmail.com'),
(2, 'Target', '50000002', 'Target@gmail.com'),
(3, 'IKEA', '50000003', 'IKEA@gmail.com'),
(4, 'MUJI', '50000004', 'MUJI@gmail.com'),
(5, 'Dyson', '50000005', 'Dyson@gmail.com');

INSERT INTO Customer (Customer_ID, Customer_Name, Customer_Phone, Customer_Email) VALUES
(1, 'John Doe',   '80000001', 'JohnDoe@gmail.com'),
(2, 'Jane Doe',   '80000002', 'JaneDoe@gmail.com'),
(3, 'John Smith', '80000003', 'JohnSmith@gmail.com'),
(4, 'Jane Smith',  '80000004', 'JaneSmith@gmail.com'),
(5, 'Joe Smith',  '80000005', 'JoeSmith@gmail.com');

INSERT INTO Product_Category (Category_ID, Category_Name) VALUES
(1, 'Electronics'),
(2, 'Home'),
(3, 'Kitchen'),
(4, 'Fitness'),
(5, 'Accessories/Misc');

INSERT INTO Product (Product_ID, Product_Description, Standard_Price, Vendor_ID) VALUES
(1, 'Wireless Keyboard', 25.90, 1),
(2, 'USB Charger', 18.00, 2),
(3, 'Thumbdrive', 12.50, 3),
(4, 'Water Bottle', 15.00, 4),
(5, 'Canvas Bag', 6.50, 5);

INSERT INTO Orders (Order_ID, Order_date, Customer_ID) VALUES
(1, '2025-01-10', 1),
(2, '2025-01-12', 2),
(3, '2025-01-14', 3),
(4, '2025-01-16', 4),
(5, '2025-01-18', 5);

INSERT INTO OrderDetails (Order_ID, Product_ID, Ordered_Quantity, Price_per_Quantity) VALUES
(1, 1, 2, 24.50),
(2, 2, 1, 16.50),
(3, 3, 3, 12.00),
(4, 4, 1, 12.50),
(5, 5, 4,  6.00);

INSERT INTO Product_Assignment (Product_ID, Category_ID) VALUES
(1, 1),
(2, 1),
(3, 1),
(4, 4),
(5, 5);

INSERT INTO Customer_Address
(Address_ID, Customer_ID, Address_line_1, Address_line_2, City, State, Country, Postal_Code) VALUES
(1, 1, '123 Ford Street', 'Unit 01-01', 'Singapore', 'Singapore', 'Singapore', '100001'),
(2, 2, '45 Downtown Street', 'Unit 02-02', 'Singapore', 'Singapore', 'Singapore', '100002'),
(3, 3, '67 Alpha Street', 'Unit 03-03', 'Singapore', 'Singapore', 'Singapore', '100003'),
(4, 4, '89 Happy Street', 'Unit 04-04', 'Singapore', 'Singapore', 'Singapore', '100004'),
(5, 5, '10 NoWhere Street', 'Unit 05-05', 'Singapore', 'Singapore', 'Singapore', '100005');

INSERT INTO Vendor_Address
(Address_ID, Vendor_ID, Address_line_1, Address_line_2, City, State, Country, Postal_Code) VALUES
(1, 1, '12 Stevens Road', 'Level 1', 'Singapore', 'Singapore', 'Singapore', '200001'),
(2, 2, '2 Pierre Road', 'Level 2', 'Singapore', 'Singapore', 'Singapore', '200002'),
(3, 3, '3 Stone Road', 'Level 3', 'Singapore', 'Singapore', 'Singapore', '200003'),
(4, 4, '4 Rock Road', 'Level 4', 'Singapore', 'Singapore', 'Singapore', '200004'),
(5, 5, '5 Boulder Road', 'Level 5', 'Singapore', 'Singapore', 'Singapore', '200005');

INSERT INTO Shipment
(Shipment_ID, Order_ID, Customer_ID, Vendor_ID, Shipping_Date, Arrival_Date, Shipment_Amount) VALUES
(1, 1, 1, 1, '2025-02-11', '2025-03-13', 5.00),
(2, 2, 2, 2, '2025-03-12', '2025-04-15', 6.00),
(3, 3, 3, 3, '2025-04-13', '2025-05-17', 5.50),
(4, 4, 4, 4, '2025-05-14', '2025-06-19', 7.00),
(5, 5, 5, 5, '2025-06-15', '2025-07-21', 4.50);

-- Query View Code

-- List products under $15 (very affordable products) using vendor and category.
-- Where prices less than 15 dollars order by ascending order of price
CREATE VIEW order_cheap_products_view AS
SELECT
  Product_ID, Product_Description, Standard_Price
FROM Product
WHERE Standard_Price < 15;

-- joining multi-table
-- order line details with caculated linetotal
CREATE VIEW order_line_details_view AS
SELECT
  o.Order_ID,
  o.Order_date,
  c.Customer_Name,
  p.Product_Description,
  v.Vendor_Name,
  d.Ordered_Quantity,
  d.Price_per_Quantity,
  (d.Ordered_Quantity * d.Price_per_Quantity) AS LineTotal
FROM Orders o
JOIN Customer c ON c.Customer_ID = o.Customer_ID
JOIN OrderDetails d ON d.Order_ID = o.Order_ID
JOIN Product p ON p.Product_ID = d.Product_ID
JOIN Vendor v ON v.Vendor_ID = p.Vendor_ID;

-- customer order count
CREATE VIEW customer_order_count_view AS
SELECT
  c.Customer_ID,
  c.Customer_Name,
  COUNT(o.Order_ID) AS NumberOfOrders
FROM Customer c
LEFT JOIN Orders o ON o.Customer_ID = c.Customer_ID
GROUP BY c.Customer_ID, c.Customer_Name;

-- order totals for all orders
CREATE VIEW view_order_totals_view AS
SELECT
  o.Order_ID,
  o.Order_date,
  c.Customer_ID,
  c.Customer_Name,
  SUM(od.Ordered_Quantity * od.Price_per_Quantity) AS OrderTotal
FROM Orders o
JOIN Customer c ON c.Customer_ID = o.Customer_ID
JOIN OrderDetails od ON od.Order_ID = o.Order_ID
GROUP BY o.Order_ID, o.Order_date, c.Customer_ID, c.Customer_Name;

-- order totals but filtered by only those above 10 dollars (higher priced items)
CREATE VIEW view_expensive_order_totals_view AS
SELECT
  o.Order_ID,
  c.Customer_Name,
  SUM(d.Ordered_Quantity * d.Price_per_Quantity) AS OrderTotal
FROM Orders o
JOIN Customer c ON c.Customer_ID = o.Customer_ID
JOIN OrderDetails d ON d.Order_ID = o.Order_ID
GROUP BY o.Order_ID, c.Customer_Name
HAVING OrderTotal >= 10;


