<?php

// Function to establish database connection
function connectToDatabase() {
    $servername = "127.0.0.1";
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
    $min = -1;
    $max = -1;
    foreach ($menu as $item) {
        $tag =  mysqli_real_escape_string($conn, $item['category_name']);
        $name = mysqli_real_escape_string($conn, $item['name']);
        $price = intval($item['price']);
        $img = key_exists('food_image_url', $item) ? mysqli_real_escape_string($conn, $item['food_image_url']) : null;
        $description = key_exists('product_description', $item) ? mysqli_real_escape_string($conn, $item['product_description']) : null;
        $created_at = date('Y-m-d H:i:s');
        $updated_at = date('Y-m-d H:i:s');

        $sql = "INSERT INTO `meals`(`rid`, `tag`, `name`, `price`, `img`, `description`, `created_at`, `updated_at`) 
                VALUES ('$restaurant_id', '$tag', '$name', '$price', '$img', '$description', '$created_at', '$updated_at')";

        try{
            $conn->query($sql);

            if ($min == -1 || $price < $min) {
                $min = $price;
            }
            if ($price > $max) {
                $max = $price;
            }

        } catch (Exception $e) {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
        // if ($conn->query($sql) === TRUE) {
        //     // Meal inserted successfully
        // } else {
        //     echo "Error: " . $sql . "<br>" . $conn->error;
        // }
    }
    $sql = 'UPDATE `restaurant` SET `min_price` = ' . $min . ', `max_price` = ' . $max . ' WHERE `id` = ' . $restaurant_id;
}

// Function to insert data into the 'restaurant' table
function insertRestaurant($conn, $restaurant) {
    
    $name = mysqli_real_escape_string($conn, $restaurant['name']);
    // ckeck if the restaurant exists with name
    $sql = "SELECT * FROM `restaurant` WHERE `name` = '" . $name . "'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        echo "Restaurant " . $restaurant['name'] . " already exists\n";
        return null;
    }

    $business_registration = mysqli_real_escape_string($conn, json_encode($restaurant['business_registration'] ?? '', JSON_UNESCAPED_UNICODE));
    $uniform_numbers = mysqli_real_escape_string($conn, $restaurant['uniform_numbers']);
    $address = mysqli_real_escape_string($conn, $restaurant['address']);
    $tel = mysqli_real_escape_string($conn, $restaurant['tel']);
    $url_order = mysqli_real_escape_string($conn, $restaurant['url_order']);
    $service_hours_text = mysqli_real_escape_string($conn, $restaurant['service_hours_text']);
    $announcement = mysqli_real_escape_string($conn, $restaurant['announcement']);
    $delivery_rules = mysqli_real_escape_string($conn, json_encode($restaurant['delivery_rules'] ?? '', JSON_UNESCAPED_UNICODE));
    $thumbnailImageUrl = mysqli_real_escape_string($conn, $restaurant['thumbnailImageUrl'] ?? '/images/meals-table.png'); 
    $note = mysqli_real_escape_string($conn, $restaurant['note']);
    $created_at = date('Y-m-d H:i:s');
    $updated_at = date('Y-m-d H:i:s');

    $sql = "INSERT INTO `restaurant`(`name`, `business_registration`, `uniform_numbers`, `address`, `tel`, `url_order`, `service_hours_text`, `announcement`, `delivery_rules`, `created_at`, `updated_at`, `thumbnailImageUrl`, `note`)
            VALUES ('$name', '$business_registration', '$uniform_numbers', '$address', '$tel', '$url_order', '$service_hours_text', '$announcement', '$delivery_rules', '$created_at', '$updated_at', '$thumbnailImageUrl', '$note')";

    try {
        $conn->query($sql);
        echo "$conn->insert_id: $name created successfully for meals\n";
        return $conn->insert_id;
    } catch (Exception $e){
        echo "Error: " . $sql . "<br>" . $conn->error;
        return null;
    }
}

// Main script
$filename = "data.json";
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

// count id group by note
$sql = "SELECT note, COUNT(id) as count FROM restaurant GROUP BY note";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    echo "Note count\n";
    while($row = $result->fetch_assoc()) {
        echo $row['note'] . ": " . $row['count'] . "\n";
    }
}

$conn->close();

echo "Done\n";

?>
