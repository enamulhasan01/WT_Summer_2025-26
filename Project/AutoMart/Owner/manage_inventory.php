<?php
session_start();
if(!isset($_SESSION["email"]) || $_SESSION["role"] !== "Owner") {
    header("Location: ../login.php");
    exit();
}
include '../db.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Inventory - AutoMart</title>
    <style>
        * { box-sizing: border-box; font-family: Arial, sans-serif; }
        body { margin: 0; padding: 0; background-color: #0a192f; color: white; }
        .sidebar { width: 20%; float: left; height: 100vh; background-color: #000000; padding: 20px; position: fixed; }
        .sidebar h2 { color: #ffffff; margin-bottom: 40px; text-align: center; font-size: 18px; text-transform: uppercase;}
        .sidebar a { display: block; color: white; padding: 12px 15px; text-decoration: none; margin-bottom: 10px; border-radius: 20px; background-color: #555555; text-align: center; font-weight: bold; font-size: 13px;}
        .sidebar a.active, .sidebar a:hover { background-color: #6b6bff; }
        .sidebar a.logout { background-color: #cc0000; position: absolute; bottom: 20px; width: calc(100% - 40px); }

        .main-content { width: 80%; float: right; padding: 40px; height: 100vh; overflow-y: auto; }
        h1 { margin-top: 0; font-size: 24px; font-weight: normal; text-transform: uppercase; margin-bottom: 30px;}

        .top-bar { display: flex; gap: 15px; margin-bottom: 15px; }
        .search-bar { flex-grow: 1; padding: 12px 20px; border-radius: 8px; border: none; background-color: #cccccc; font-size: 16px; }
        .filter-btn { padding: 12px 30px; border-radius: 8px; border: none; background-color: #cccccc; font-weight: bold; cursor: pointer; }
        .add-btn { display: block; width: 100%; padding: 12px; border-radius: 8px; border: none; background-color: #7b6b8f; color: white; text-align: center; text-decoration: none; font-weight: bold; margin-bottom: 30px;}
        .add-btn:hover { background-color: #5c4e6e; }

        .car-card { background-color: #cccccc; color: black; padding: 20px; border-radius: 15px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;}
        .car-info h3 { margin: 0 0 5px 0; font-size: 18px; }
        .car-info p { margin: 0 0 15px 0; font-size: 13px; font-weight: bold;}
        .badge { padding: 6px 15px; border-radius: 20px; font-size: 12px; font-weight: bold; color: white; display: inline-block; margin-right: 10px;}
        .badge.stock { background-color: #5b9bd5; }
        .badge.profit { background-color: #70ad47; }
        .car-image { width: 200px; height: auto; border-radius: 10px;}
    </style>
</head>
<body>
    <div class="sidebar">
        //
    </div>

    <div class="main-content">
        <h1>Manage Inventory</h1>
        <div class="top-bar">
            <input type="text" class="search-bar" placeholder="Search Cars...">
            <button class="filter-btn">Filter</button>
        </div>
        <a href="add_vehicle.php" class="add-btn">+ Add New Vehicle</a>

        <?php
        $sql = "SELECT * FROM VEHICLE ORDER BY Vehicle_Id DESC LIMIT 10";
        $result = $conn->query($sql);
        while($row = $result->fetch_assoc()) {
            $imagePath = !empty($row["Image"]) ? '../Assets/' . $row["Image"] : '../Assets/default_car.png';
            echo '
            <div class="car-card">
                <div class="car-info">
                    <h3>' . $row["Year"] . ' ' . $row["Make"] . ' ' . $row["Model"] . '</h3>
                    <p>Listed: $' . number_format($row["Listed_Price"]) . '</p>
                    <span class="badge stock">In Stock</span>
                    <span class="badge profit">Profitable</span>
                </div>
                <img src="' . $imagePath . '" class="car-image">
            </div>';
        }
        ?>
    </div>
</body>
</html>