<?php
include "config.php";




$success = "";
$error = "";
$display_data = "";




if ($_SERVER["REQUEST_METHOD"] == "POST") {
    



    $id = $_POST["id"]; 
    $firstname = $_POST["firstname"];
    $lastname = $_POST["lastname"];
    $username = $_POST["username"];
    $password = $_POST["password"];
    $email = $_POST["email"];
    
    


    $action = $_POST["action"]; 




    
    if ($action == "Insert") {
        if (empty($username) || empty($email)) {
            $error = "Username and Email are required to register!";
        } else {
            $sql = "INSERT INTO users (firstname, lastname, username, password, email) VALUES ('$firstname', '$lastname', '$username', '$password', '$email')";
            if ($conn->query($sql) === TRUE) {
                $success = "New user inserted successfully!";
            } else {
                $error = "Error: " . $conn->error;
            }
        }



    }
    



    
    elseif ($action == "Update") {
        if (empty($id)) {
            $error = "You must provide an ID to Update a record!";
        } else {
            $sql = "UPDATE users SET firstname='$firstname', lastname='$lastname', username='$username', password='$password', email='$email' WHERE id='$id'";
            if ($conn->query($sql) === TRUE) {
                $success = "Record updated successfully!";
            } else {
                $error = "Error updating: " . $conn->error;
            }
        }
    }



    
    elseif ($action == "Delete") {
        if (empty($id)) {
            $error = "You must provide an ID to Delete a record!";
        } else {
            $sql = "DELETE FROM users WHERE id='$id'";
            if ($conn->query($sql) === TRUE) {
                $success = "Record deleted successfully!";
            } else {
                $error = "Error deleting: " . $conn->error;
            }
        }
    }



    
    elseif ($action == "Search") {
        if (empty($id)) {
            $error = "Provide an ID to Search!";
        } else {
            $sql = "SELECT id, firstname, lastname, username, email FROM users WHERE id='$id'";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                $success = "Record found!";
                while($row = $result->fetch_assoc()) {
                    $display_data .= "ID: " . $row["id"] . " | Name: " . $row["firstname"] . " " . $row["lastname"] . " | Username: " . $row["username"] . "<br>";
                }
            } else {
                $error = "0 results found.";
            }
        }


    }

    


    elseif ($action == "Display All") {
        $sql = "SELECT id, firstname, lastname, username, email FROM users";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            $success = "Displaying all users!";
            while($row = $result->fetch_assoc()) {
                $display_data .= "ID: " . $row["id"] . " | Name: " . $row["firstname"] . " " . $row["lastname"] . " | Username: " . $row["username"] . "<br>";
            }
        } else {
            $error = "No users found in database.";
        }
    }




    
    elseif ($action == "Sort by First Name") {
        $sql = "SELECT id, firstname, lastname, username, email FROM users ORDER BY firstname ASC";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            $success = "Displaying users sorted alphabetically!";
            while($row = $result->fetch_assoc()) {
                 $display_data .= "ID: " . $row["id"] . " | Name: " . $row["firstname"] . " " . $row["lastname"] . " | Username: " . $row["username"] . "<br>";
            }
        } else {
            $error = "0 results.";
        }


    }
}
?>

<html>
<head>
    <title>6 Operations CRUD</title>
</head>
<body>
    <h2>Lab_1 Database Form (6 Inputs & 6 Operations)</h2>
    
    


    <form method="post" action="">
        <b>(Input your ID for Update, Delete, and Search)</b><br>
        ID: <input type="number" name="id"><br><br>
        First Name: <input type="text" name="firstname"><br><br>
        Last Name: <input type="text" name="lastname"><br><br>
        Username: <input type="text" name="username"><br><br>
        Password: <input type="password" name="password"><br><br>
        Email: <input type="email" name="email"><br><br>
        
        


        <input type="submit" name="action" value="Insert">
        <input type="submit" name="action" value="Update">
        <input type="submit" name="action" value="Delete">
        <input type="submit" name="action" value="Search">
        <input type="submit" name="action" value="Display All">
        <input type="submit" name="action" value="Sort by First Name">
    </form>
    



    <h3 style="color: green;"><?php echo $success; ?></h3>
    <h3 style="color: red;"><?php echo $error; ?></h3>
    
    


    <p>
        <?php echo $display_data; ?>
    </p>
    

    
</body>
</html>