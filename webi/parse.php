<?php

function parse($platform)
{
    $jsonData = file_get_contents("output/$platform.json");
    $data = json_decode($jsonData, true);
    $parsedData = [];
    foreach ($data as $item) {

        if (count($item['menu']) == 0) {
            continue;
        }
        if (!isset($item['is_delivery']) || $item['is_delivery'] == 0) {
            continue;
        }

        // Extract necessary information
        $deliveryConditions = null;
        if (isset($company['delivery']) )
        {
            foreach ($company['delivery'] as $delivery) {
                if ($delivery['type'] == 'delivery') {
                    $deliveryConditions = $delivery;
                    break;
                }
            }
        }

        $menu = [];
        foreach ($item['menu'] as $menu_item) {
            $tag = $menu_item['category_name'];
            foreach ($menu_item['items'] as $item) {
                $name = mysqli_real_escape_string($conn, $item['title']);
                $price = $item['price'];
                $img = key_exists('food_image_url', $item) ? $item['food_image_url'] : null;
                $description = key_exists('product_description', $item) ? mysqli_real_escape_string($conn, $item['product_description']) : null;
                $menu[] = [
                    "category_name" => $tag,
                    "name" => $name,
                    "price" => $price,
                    "img" => $img,
                    "description" => $description,
                    "created_at" => date('Y-m-d H:i:s'),
                    "updated_at" => date('Y-m-d H:i:s')
                ];
            }
        }
        // Construct the parsed data for the current item
        $parsedItem = [
            "name" => $item['name'] ?? "",
            "business_registration" => "",
            "uniform_numbers" => "",
            "lat" => "",
            "lng" => "",
            "area" => "",
            "address" => $item['address'] ?? "",
            "tel" => $item['phone'] ?? "",
            "email" => "",
            "thumbnailImageUrl" => $item['image'] ?? "",
            "google_map_url" => "",
            "url_line" => $item['line_link'] ?? "",
            "url_order" => $item['store_link'] ?? "",
            "service_hours_text" => "",
            "announcement" => "",
            "delivery_rules" =>"",
            "business_hour" => json_encode($deliveryConditions, JSON_UNESCAPED_UNICODE),
            "note" => $platform,
            "created_at" => date('Y-m-d H:i:s'), // Now
            "updated_at" => date('Y-m-d H:i:s'), // Now
            "payment" => json_encode($item['payment_method'] ?? [], JSON_UNESCAPED_UNICODE),
            "menu" => $item['menu'] ?? [],
        ];

        // Add the parsed item to the parsed data array
        $parsedData[] = $parsedItem;
    }
    return $parsedData;
}

$PLATFORMS = ['iding', 'go_foodie'];

$result = [];
foreach ($PLATFORMS as $platform) {
    $result = array_merge($result, parse($platform));
}

// Encode the parsed data into JSON format
$parsedJson = json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

// Write the parsed JSON data into a new file
file_put_contents("data.json", $parsedJson);

echo "Parsed data has been written to output.json\n";

?>
