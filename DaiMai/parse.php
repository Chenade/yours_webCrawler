<?php

// Read JSON file
$json_data = file_get_contents('your_json_file.json');

// Decode JSON data
$data = json_decode($json_data, true);

// Function to convert data
function convertData($inputData) {
    $convertedData = [];
    foreach ($inputData as $item) {
        // Check if "可外送" is in the available methods
        if (count($item['menu']) == 0) continue;
        if (in_array("可外送", $item['available_methods']) && isset($item['company']['list'][0])) {
            $company = $item['company']['list'][0];
            $deliveryConditions = null;
            if (
                isset($company['pickup']) &&
                isset($company['pickup']['delivery']) &&
                is_array($company['pickup']['delivery']) &&
                isset($company['pickup']['delivery']['conditions'])
            ) {
                $deliveryConditions = $company['pickup']['delivery']['conditions'];
            }

            $menu = [];
            foreach ($item['menu'] as $menu_item) {
                $tag = $menu_item['category_name'];
                foreach ($menu_item['items'] as $item) {
                    $name = mysqli_real_escape_string($conn, $item['title']);
                    $price = $item['price'];
                    $img = key_exists('img', $item) ? $item['img'] : null;
                    $description = key_exists('description', $item) ? mysqli_real_escape_string($conn, $item['description']) : null;
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

            $convertedItem = [
                "name" => $item['name'] ?? "",
                "business_registration" => $company['business_registration'] ?? "",
                "uniform_numbers" => $company['uniform_numbers'] ?? "",
                "lat" => "",
                "lng" => "",
                "area" => "",
                "address" => $company['address'] ?? "",
                "tel" => $company['phone'] ?? "",
                "email" => "",
                "thumbnailImageUrl" => $item['banner'] ?? "",
                "google_map_url" => "",
                "url_line" => $item['line_link'] ?? "",
                "url_order" => "https://imenu.com.tw/". $item["company_slug"] .'/' . $item['slug'] . "/menu",
                "service_hours_text" => $company['service_hours_text'] ?? "",
                "announcement" => $company['pickup']['delivery']['description'] ?? "",
                "delivery_rules" => json_encode($deliveryConditions, JSON_UNESCAPED_UNICODE),
                "business_hour" => "",
                "note" => "DaiMai",
                "created_at" => date('Y-m-d H:i:s'), // Now
                "updated_at" => date('Y-m-d H:i:s'), // Now
                "menu" => $menu
            ];
            $convertedData[] = $convertedItem;
        }
    }
    return $convertedData;
}

// Convert data
$convertedData = convertData($data);

// Encode converted data as JSON
$convertedJson = json_encode($convertedData, JSON_PRETTY_PRINT);

// Write JSON data to another file
file_put_contents('data.json', $convertedJson);

echo "Conversion completed. Data written to converted_data.json";
?>
