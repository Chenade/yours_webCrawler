<?php

function connectToDatabase() {
    $servername = "localhost";
    $username = "admin";
    $password = "";
    $dbname = "yourshealth";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    return $conn;
}

function insertRestaurant($conn, $data) {
    $name = $data['name'];

    // Check if restaurant with the same name already exists
    $check_query = "SELECT * FROM `restaurant` WHERE `name` = '$name'";
    $check_result = $conn->query($check_query);

    if ($check_result->num_rows > 0) {
        echo "Restaurant with name '$name' already exists. Skipping insertion.\n";
        return; 
    }

    $id = $data['id'];
    $lat = key_exists('lat', $data) ? $data['lat'] : null;
    $lng = key_exists('lng', $data) ? $data['lng'] : null;
    $area = key_exists('area', $data) ? $data['area'] : null;
    $address = $data['address'];
    $tel = key_exists('order_phone', $data) ? $data['order_phone'] : null;
    $email = key_exists('email', $data) ? $data['email'] : null;
    $thumbnailImageUrl = key_exists('banner', $data) ? $data['banner'] : null;
    $url_order = "https://delicacy.maifood.com.tw/stores/$id";
    $url_line = key_exists('line_link', $data) ? $data['line_link'] : null;
    $google_map_url = key_exists('google_map_url', $data) ? $data['google_map_url'] : null;
    $note = "daimei";
    $created_at = date('Y-m-d H:i:s');
    $updated_at = date('Y-m-d H:i:s');

    $sql = "INSERT INTO `restaurant`(`id`, `name`, `lat`, `lng`, `area`, `address`, `tel`, `email`, `thumbnailImageUrl`, `url_order`, `url_line`, `google_map_url`, `note`, `created_at`, `updated_at`) VALUES ('$id', '$name', '$lat', '$lng', '$area', '$address', '$tel', '$email', '$thumbnailImageUrl', '$url_order', '$url_line', '$google_map_url', '$note', '$created_at', '$updated_at')";

    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully for restaurant $name\n";
        $restaurant_id = $conn->insert_id;
        $menu = $data['menu'];
        insertMeals($conn, $restaurant_id, $menu);
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Function to insert data into the 'meals' table
function insertMeals($conn, $restaurant_id, $menu) {
    foreach ($menu as $category) {
        $tag = $category['category_name'];
        foreach ($category['items'] as $item) {
            $id = $item['id'];
            $name = $item['title'];
            $price = $item['price'];
            $description = key_exists('description', $item) ? $item['description'] : null;
            $created_at = date('Y-m-d H:i:s');
            $updated_at = date('Y-m-d H:i:s');

            $sql = "INSERT INTO `meals`(`id`, `rid`, `tag`, `name`, `price`, `description`, `created_at`, `updated_at`) VALUES ('$id', '$restaurant_id', '$tag', '$name', '$price', '$description', '$created_at', '$updated_at')";

            if ($conn->query($sql) === TRUE) {


            } else {
                echo "Error: " . $sql . "<br>" . $conn->error;
            }
        }
    }
    echo "New record created successfully for meal $name\n";
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
    insertRestaurant($conn, $restaurant);
}

$conn->close();

echo "Done\n";
?>
