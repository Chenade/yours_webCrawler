<?php

include '.env.php';

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
function insertMeals($conn, $restaurant_id, $menu)
{    
    $_img = null;
    foreach ($menu as $item) {
        $tag =  mysqli_real_escape_string($conn, $item['category_name']);
        $name = mysqli_real_escape_string($conn, $item['name']);
        $price = intval($item['price']);
        $img = key_exists('food_image_url', $item) ? mysqli_real_escape_string($conn, $item['food_image_url']) : null;
        $description = key_exists('product_description', $item) ? mysqli_real_escape_string($conn, $item['product_description']) : null;
        $created_at = date('Y-m-d H:i:s');
        $updated_at = date('Y-m-d H:i:s');
        if (!$_img) $_img = $img;

        $sql = "INSERT INTO `meals`(`rid`, `tag`, `name`, `price`, `img`, `description`, `created_at`, `updated_at`) 
                VALUES ('$restaurant_id', '$tag', '$name', '$price', '$img', '$description', '$created_at', '$updated_at')";

        try{
            $conn->query($sql);
        } catch (Exception $e) {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }

    $min = -1;
    $max = -1;
    $meals_sql = "SELECT price FROM `meals` WHERE `rid` = " . $restaurant_id . " AND `price` > 0 ORDER BY price" ;
    $result = $conn->query($meals_sql);
    if ($result->num_rows > 0)
    {
        $min_index = floor($result->num_rows / 4) - 1 ;
        $min_index = ($min_index > 0) ? $min_index - 1 : 0;
        $max_index = ceil($result->num_rows * 3 / 4) -1 ;
        $max_index = ($max_index < $result->num_rows - 1) ? $max_index + 1 : $result->num_rows - 1;
        $result->data_seek($min_index);
        $min = $result->fetch_all(MYSQLI_ASSOC)[0]['price'];
        $result->data_seek($max_index);
        $max = $result->fetch_all(MYSQLI_ASSOC)[0]['price'];
    }

    try{
        $sql = "UPDATE `restaurant` SET `min_price` = $min, `max_price` = $max WHERE `id` = $restaurant_id";
        $conn->query($sql);

        $resto = "SELECT * FROM `restaurant` WHERE `id` = $restaurant_id";
        $result = $conn->query($resto);
        if ($result->num_rows > 0)
        {
            $row = $result->fetch_assoc();
            if ($row['img'] == '') {
                $sql = "UPDATE `restaurant` SET `img` = '$img' WHERE `id` = $restaurant_id";
                $conn->query($sql);
            }
        } 

    } catch (Exception $e) {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

function get_lat_lng($address) {
    $address = urlencode($address);
    $url = "https://maps.googleapis.com/maps/api/geocode/json?address=$address&key=$key";
    $response = file_get_contents($url);
    $response = json_decode($response, true);
    if ($response['status'] == 'OK') {
        $lat = $response['results'][0]['geometry']['location']['lat'];
        $lng = $response['results'][0]['geometry']['location']['lng'];
        foreach ($response['results'][0]['address_components'] as $component) {
            if (in_array('postal_code', $component['types'])) {
                $area = intval($component['long_name']);
                break;
            }
        }
        return [$lat, $lng, $area];
    }
    return [null, null, null];
}

// Function to insert data into the 'restaurant' table
function insertRestaurant($conn, $restaurant) {
    
    $name = mysqli_real_escape_string($conn, $restaurant['name']);
    // ckeck if the restaurant exists with name
    $sql = "SELECT * FROM `restaurant` WHERE `name` = '" . $name . "'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        echo "Restaurant " . $restaurant['name'] . " already exists\n";
        if ($result->num_rows > 1) 
        {
            echo "Error: Duplicate restaurant name\n";
        } else 
        {
		$row = $result->fetch_assoc();
		echo $row['lat'] . "\n";
            if (strlen($row['area']) > 3)
            {
                $area = substr($row['area'], 0, 3);
                $sql = "UPDATE `restaurant` SET `area` = $area WHERE `id` = " . $row['id'];
                $conn->query($sql);
                echo "Updated area for " . $restaurant['name'] . "\n";
	    }
            if (is_null($row['lat']) || is_null($row['lng']) || is_null($row['area']))
            //if ($row['lat'] == NULL || $row['lng'] == NULL || $row['area'] == NULL)
            {
                $google_map = get_lat_lng($restaurant['address']);
                $lat = $google_map[0] ?? 0;
                $lng = $google_map[1] ?? 0;
                $area = $google_map[2] ?? '';
                $sql = "UPDATE `restaurant` SET `lat` = $lat, `lng` = $lng, `area` = $area WHERE `id` = " . $row['id'];
                try{
                    $conn->query($sql);
                    echo "Updated lat, lng, area for " . $restaurant['name'] . "\n";
                } catch (Exception $e) {
                    echo "Error: " . $sql . "<br>" . $conn->error;
                }
            }
        }
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
    $google_map = get_lat_lng($address);
    $lat = $google_map[0] ?? 0;
    $lng = $google_map[1] ?? 0;
    $area = $google_map[2] ?? null;

    $sql = "INSERT INTO `restaurant`(`name`, `business_registration`, `uniform_numbers`, `address`, `tel`, `url_order`, `service_hours_text`, `announcement`, `delivery_rules`, `created_at`, `updated_at`, `thumbnailImageUrl`, `note`, `lat`, `lng`, `area`)
            VALUES ('$name', '$business_registration', '$uniform_numbers', '$address', '$tel', '$url_order', '$service_hours_text', '$announcement', '$delivery_rules', '$created_at', '$updated_at', '$thumbnailImageUrl', '$note', '$lat', '$lng', '$area')";

    try {
        $conn->query($sql);
        echo "$conn->insert_id: $name created successfully.\n";
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
