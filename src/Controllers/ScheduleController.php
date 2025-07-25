<?php

namespace Ct27501Project\Controllers;

use Ct27501Project\Controllers\Controller;
use Ct27501Project\Models\Route;
use Ct27501Project\Models\Schedule;

class ScheduleController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Hiển thị trang tìm kiếm các chuyến (routes)
     */
    public function index()
    {
        $routes = [];
        $routesByCity = [];
        $searchParams = [];

        // Kiểm tra có tham số tìm kiếm không - Updated to remove removed fields
        if (!empty($_GET)) {
            $searchParams = [
                'start_point' => $_GET['start_point'] ?? '',
                'end_point' => $_GET['end_point'] ?? '',
                'price_range' => $_GET['price_range'] ?? ''
            ];

            // Thực hiện tìm kiếm nếu có ít nhất 1 tham số
            if (array_filter($searchParams)) {
                $routes = $this->searchRoutesWithScheduleInfo($searchParams);
            }
        } else {
            // Mặc định hiển thị tất cả chuyến
            $routes = $this->getAllRoutesWithScheduleInfo();
        }

        // Lấy danh sách loại xe từ database (keep for other purposes)
        $busTypes = Schedule::getBusTypes();

        // Nhóm chuyến theo thành phố xuất phát
        foreach ($routes as $route) {
            $city = $route['start_point'];
            if (!isset($routesByCity[$city])) {
                $routesByCity[$city] = [];
            }
            $routesByCity[$city][] = $route;
        }

        // Sắp xếp theo tên thành phố
        ksort($routesByCity);

        // Use the parent's sendPage method correctly
        return parent::sendPage('users/routes', [
            'routes' => $routes,
            'routesByCity' => $routesByCity,
            'searchParams' => $searchParams,
            'busTypes' => $busTypes
        ]);
    }

    /**
     * Render page using template system
     */
    public function sendPage($template, $data = [])
    {
        // Extract data to variables
        extract($data);

        // Include the template file
        $templatePath = __DIR__ . '/../views/' . $template . '.php';
        if (file_exists($templatePath)) {
            include $templatePath;
        } else {
            echo "Template not found: $template";
        }
    }

    /**
     * API để lấy các tùy chọn filter
     */
    public function getFilterOptions()
    {
        $schedules = Schedule::all();

        // Tạo các tùy chọn khung giờ
        $timeRanges = [
            '00:00-06:00' => 'Sáng sớm (00:00 - 06:00)',
            '06:00-12:00' => 'Buổi sáng (06:00 - 12:00)',
            '12:00-18:00' => 'Buổi chiều (12:00 - 18:00)',
            '18:00-24:00' => 'Buổi tối (18:00 - 24:00)'
        ];

        // Lấy các loại xe duy nhất
        $busTypes = [];
        foreach ($schedules as $schedule) {
            if (!in_array($schedule->bus_type, $busTypes)) {
                $busTypes[] = $schedule->bus_type;
            }
        }

        // Tạo các mức giá
        $priceRanges = [
            '0-100000' => 'Dưới 100,000đ',
            '100000-200000' => '100,000đ - 200,000đ',
            '200000-300000' => '200,000đ - 300,000đ',
            '300000-500000' => '300,000đ - 500,000đ',
            '500000-1000000' => 'Trên 500,000đ'
        ];

        header('Content-Type: application/json');
        echo json_encode([
            'timeRanges' => $timeRanges,
            'busTypes' => $busTypes,
            'priceRanges' => $priceRanges
        ]);
    }
    /**
     * Lấy danh sách loại xe từ database
     */
    private function getBusTypesFromDatabase()
    {
        try {
            global $PDO;
            $stmt = $PDO->query("
                SELECT DISTINCT bus_type 
                FROM buses 
                WHERE bus_type IS NOT NULL 
                ORDER BY bus_type
            ");
            $busTypes = [];
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                if (!empty($row['bus_type'])) {
                    $busTypes[] = $row['bus_type'];
                }
            }
            return $busTypes;
        } catch (\Exception $e) {
            error_log("Error fetching bus types: " . $e->getMessage());
            return [];
        }
    }

    /**
     * API endpoint to get schedule details for booking
     */
    public function getScheduleDetails($scheduleId)
    {
        try {
            $schedule = Schedule::find($scheduleId);

            if (!$schedule) {
                http_response_code(404);
                return json_encode(['error' => 'Schedule not found']);
            }

            header('Content-Type: application/json');
            return json_encode([
                'success' => true,
                'schedule' => [
                    'schedule_id' => $schedule->schedule_id,
                    'route_start' => $schedule->route_start,
                    'route_end' => $schedule->route_end,
                    'departure_time' => $schedule->departure_time,
                    'arrival_time' => $schedule->arrival_time,
                    'price' => $schedule->price,
                    'bus_type' => $schedule->bus_type,
                    'bus_license_plate' => $schedule->bus_license_plate,
                    'driver_name' => $schedule->driver_name,
                    'available_seats' => $schedule->available_seats,
                    'distance_km' => $schedule->distance_km
                ]
            ]);
        } catch (\Exception $e) {
            error_log("Error getting schedule details: " . $e->getMessage());
            http_response_code(500);
            return json_encode(['error' => 'Internal server error']);
        }
    }

    /**
     * Lấy tất cả chuyến với thông tin lịch trình
     */
    private function getAllRoutesWithScheduleInfo()
    {
        try {
            global $PDO;
            $stmt = $PDO->query("
                SELECT DISTINCT 
                    r.route_id,
                    r.start_point,
                    r.end_point,
                    r.distance_km,
                    MIN(s.price) as min_price,
                    MAX(s.price) as max_price,
                    COUNT(DISTINCT s.schedule_id) as schedule_count,
                    STRING_AGG(DISTINCT b.bus_type, ', ') as available_bus_types
                FROM routes r
                LEFT JOIN schedules s ON r.route_id = s.route_id
                LEFT JOIN buses b ON s.bus_id = b.bus_id
                GROUP BY r.route_id, r.start_point, r.end_point, r.distance_km
                ORDER BY r.start_point, r.end_point
            ");
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("Error getting routes: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Tìm kiếm chuyến với thông tin lịch trình - Updated to properly handle empty price filter
     */
    private function searchRoutesWithScheduleInfo($conditions)
    {
        try {
            global $PDO;
            $sql = "
                SELECT DISTINCT 
                    r.route_id,
                    r.start_point,
                    r.end_point,
                    r.distance_km,
                    MIN(s.price) as min_price,
                    MAX(s.price) as max_price,
                    COUNT(DISTINCT s.schedule_id) as schedule_count,
                    STRING_AGG(DISTINCT b.bus_type, ', ') as available_bus_types
                FROM routes r
                LEFT JOIN schedules s ON r.route_id = s.route_id
                LEFT JOIN buses b ON s.bus_id = b.bus_id
                WHERE 1=1
            ";
            $params = [];

            // Basic search conditions
            if (!empty($conditions['start_point'])) {
                $sql .= " AND r.start_point ILIKE ?";
                $params[] = "%{$conditions['start_point']}%";
            }

            if (!empty($conditions['end_point'])) {
                $sql .= " AND r.end_point ILIKE ?";
                $params[] = "%{$conditions['end_point']}%";
            }

            // Price range filter - Only apply if price_range is not empty
            if (!empty($conditions['price_range']) && $conditions['price_range'] !== '') {
                $priceRange = explode('-', $conditions['price_range']);
                if (count($priceRange) == 2) {
                    $minPrice = (int)$priceRange[0];
                    $maxPrice = (int)$priceRange[1];
                    if ($maxPrice == 1000000) {
                        $sql .= " AND s.price >= ?";
                        $params[] = $minPrice;
                    } else {
                        $sql .= " AND s.price >= ? AND s.price <= ?";
                        $params[] = $minPrice;
                        $params[] = $maxPrice;
                    }
                }
            }

            $sql .= " GROUP BY r.route_id, r.start_point, r.end_point, r.distance_km
                     ORDER BY r.start_point, r.end_point";

            $stmt = $PDO->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("Error searching routes: " . $e->getMessage());
            return [];
        }
    }
}
