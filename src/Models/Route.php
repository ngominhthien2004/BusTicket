<?php

namespace Ct27501Project\Models;

use Exception;

class Route
{
    private $pdo;

    // Properties mapping to routes table
    public $route_id;
    public $start_point;
    public $end_point;
    public $distance_km;

    // Related data
    public $schedules = [];

    public function __construct(?\PDO $pdo = null)
    {
        $this->pdo = $pdo ?: PDO();
    }

    /**
     * Tìm tuyến đường theo ID
     */
    public static function find($id)
    {
        $route = new self();
        $stmt = $route->pdo->prepare("SELECT * FROM routes WHERE route_id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($data) {
            $route->fillFromArray($data);
            return $route;
        }
        return null;
    }

    /**
     * Lấy tất cả tuyến đường
     */
    public static function all($limit = null, $offset = 0)
    {
        $route = new self();
        $sql = "SELECT * FROM routes ORDER BY start_point, end_point";

        if ($limit) {
            $sql .= " LIMIT $limit OFFSET $offset";
        }

        $stmt = $route->pdo->query($sql);
        $routes = [];

        while ($data = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $routeObj = new self($route->pdo);
            $routeObj->fillFromArray($data);
            $routes[] = $routeObj;
        }

        return $routes;
    }

    /**
     * Tạo tuyến đường mới
     */
    public function create($data)
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO routes (start_point, end_point, distance_km)
            VALUES (?, ?, ?)
        ");

        $result = $stmt->execute([
            $data['start_point'],
            $data['end_point'],
            $data['distance_km']
        ]);

        if ($result) {
            $this->route_id = $this->pdo->lastInsertId();
            $this->start_point = $data['start_point'];
            $this->end_point = $data['end_point'];
            $this->distance_km = $data['distance_km'];
        }

        return $result;
    }

    // Get latest routes from a start point (city)
    public function getLatestRoutesByCity($city, $limit = 5)
    {
        try {
            // Debug: Log the query parameters
            error_log("Searching for routes from city: " . $city);

            // First try exact match
            $stmt = $this->pdo->prepare(
                "SELECT r.*, COALESCE(s.price, 0) as price 
                 FROM routes r 
                 LEFT JOIN schedules s ON r.route_id = s.route_id 
                 WHERE r.start_point = :city 
                 ORDER BY r.route_id DESC 
                 LIMIT :limit"
            );
            $stmt->bindValue(':city', $city);
            $stmt->bindValue(':limit', (int)$limit, \PDO::PARAM_INT);
            $stmt->execute();

            $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            error_log("Found " . count($results) . " routes for city: " . $city);

            // If no exact matches, try LIKE search
            if (empty($results)) {
                error_log("No exact matches, trying LIKE search");
                $stmt = $this->pdo->prepare(
                    "SELECT r.*, COALESCE(s.price, 0) as price 
                     FROM routes r 
                     LEFT JOIN schedules s ON r.route_id = s.route_id 
                     WHERE r.start_point LIKE :city 
                     ORDER BY r.route_id DESC 
                     LIMIT :limit"
                );
                $stmt->bindValue(':city', '%' . $city . '%');
                $stmt->bindValue(':limit', (int)$limit, \PDO::PARAM_INT);
                $stmt->execute();
                $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                error_log("LIKE search found " . count($results) . " routes");
            }

            error_log("Final results: " . print_r($results, true));
            return $results;
        } catch (Exception $e) {
            error_log("Error in getLatestRoutesByCity: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Tìm kiếm tuyến đường
     */
    public static function search($conditions, $limit = null, $offset = 0)
    {
        $route = new self();
        $sql = "SELECT * FROM routes WHERE 1=1";
        $params = [];


        if (!empty($conditions['start_point'])) {
            $sql .= " AND start_point LIKE ?";
            $params[] = "%{$conditions['start_point']}%";
        }

        if (!empty($conditions['end_point'])) {
            $sql .= " AND end_point LIKE ?";
            $params[] = "%{$conditions['end_point']}%";
        }

        $sql .= " ORDER BY start_point, end_point";

        if ($limit) {
            $sql .= " LIMIT $limit OFFSET $offset";
        }

        $stmt = $route->pdo->prepare($sql);
        $stmt->execute($params);
        $routes = [];

        while ($data = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $routeObj = new self($route->pdo);
            $routeObj->fillFromArray($data);
            $routes[] = $routeObj;
        }

        return $routes;
    }

    function exists($start_point, $end_point)
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM routes WHERE start_point = ? AND end_point = ?");
        $stmt->execute([$start_point, $end_point]);
        return $stmt->fetchColumn() > 0;
    }

    function hasSchedules($route_id)
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM schedules WHERE route_id = ?");
        $stmt->execute([$route_id]);
        return $stmt->fetchColumn() > 0;
    }

    function delete($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM routes WHERE route_id = ?");
        return $stmt->execute([$id]);
    }

    function update($data)
    {
        $stmt = $this->pdo->prepare("
            UPDATE routes SET start_point = ?, end_point = ?, distance_km = ?
            WHERE route_id = ?
        ");
        return $stmt->execute([
            $data['start_point'],
            $data['end_point'],
            $data['distance_km'],
            $data['route_id']
        ]);
    }

    /**
     * Điền dữ liệu từ mảng
     */
    private function fillFromArray($data)
    {
        $this->route_id = $data['route_id'] ?? null;
        $this->start_point = $data['start_point'] ?? null;
        $this->end_point = $data['end_point'] ?? null;
        $this->distance_km = $data['distance_km'] ?? null;
    }

    /**
     * Chuyển đổi thành mảng
     */
    public function toArray()
    {
        return [
            'route_id' => $this->route_id,
            'start_point' => $this->start_point,
            'end_point' => $this->end_point,
            'distance_km' => $this->distance_km,
            'schedules' => $this->schedules
        ];
    }
}
