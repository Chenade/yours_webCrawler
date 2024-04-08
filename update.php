<?php

// Function to establish database connection
function connectToDatabase() {
    $servername = "db";
    $username = "develop";
    $password = "developer";
    $dbname = "yourshealth";

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    return $conn;
}

// Function to insert data into the 'meals' table
function insertMeals($conn, $restaurant_id, $menu) {
    foreach ($menu as $item) {
        $tag = $item['category_name'];
        $name = $item['title'];
        $price = $item['price'];
        $img = key_exists('img', $item) ? $item['img'] : null;
        $description = key_exists('description', $item) ? $item['description'] : null;
        $created_at = date('Y-m-d H:i:s');
        $updated_at = date('Y-m-d H:i:s');

        $sql = "INSERT INTO `meals`(`rid`, `tag`, `name`, `price`, `img`, `description`, `created_at`, `updated_at`) 
                VALUES ('$restaurant_id', '$tag', '$name', '$price', '$img', '$description', '$created_at', '$updated_at')";

        if ($conn->query($sql) === TRUE) {
            // Meal inserted successfully
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }
    echo "New records created successfully for meals\n";
}

// Function to insert data into the 'restaurant' table
function insertRestaurant($conn, $restaurant) {
    $name = mysqli_real_escape_string($conn, $restaurant['name']);
    $business_registration = mysqli_real_escape_string($conn, $restaurant['business_registration']);
    $uniform_numbers = mysqli_real_escape_string($conn, $restaurant['uniform_numbers']);
    $address = mysqli_real_escape_string($conn, $restaurant['address']);
    $tel = mysqli_real_escape_string($conn, $restaurant['tel']);
    $url_order = mysqli_real_escape_string($conn, $restaurant['url_order']);
    $service_hours_text = mysqli_real_escape_string($conn, $restaurant['service_hours_text']);
    $announcement = mysqli_real_escape_string($conn, $restaurant['announcement']);
    $delivery_rules = mysqli_real_escape_string($conn, $restaurant['delivery_rules']);
    $created_at = date('Y-m-d H:i:s');
    $updated_at = date('Y-m-d H:i:s');

    $sql = "INSERT INTO `restaurant`(`name`, `business_registration`, `uniform_numbers`, `address`, `tel`, `url_order`, `service_hours_text`, `announcement`, `delivery_rules`, `created_at`, `updated_at`) 
            VALUES ('$name', '$business_registration', '$uniform_numbers', '$address', '$tel', '$url_order', '$service_hours_text', '$announcement', '$delivery_rules', '$created_at', '$updated_at')";

    if ($conn->query($sql) === TRUE) {
        return $conn->insert_id;
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
        return null;
    }
}

// Main script
$filename = "converted_data.json";
$jsonData = file_get_contents($filename);
if ($jsonData === FALSE) {
    die("Error reading file $filename");
}

$data = json_decode($jsonData, true);
$conn = connectToDatabase();

foreach ($data as $restaurant) {
    // Insert restaurant
    $restaurant_id = insertRestaurant($conn, $restaurant);

    if ($restaurant_id !== null) {
        // Insert meals for the restaurant
        insertMeals($conn, $restaurant_id, $restaurant['menu']);
    }
}

$conn->close();

echo "Done\n";

?>
