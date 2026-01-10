<?php
class BuildingController {

    private static function response($status, $message = "", $data = []) {
        echo json_encode([
            "status" => $status,
            "message" => $message,
            "data" => $data
        ]);
        exit;
    }

    // ------------------- BUILDING -------------------
    public static function addBuilding($conn, $building_name, $user_id) {
        if (empty($building_name)) self::response(false, "Building name cannot be empty");

        try {
            $stmt = $conn->prepare("INSERT INTO buildings (name, created_by) VALUES (?, ?)");
            $stmt->bind_param("si", $building_name, $user_id);
            if (!$stmt->execute()) throw new Exception($stmt->error);

            $buildingId = $stmt->insert_id;
            $stmt->close();

            self::response(true, "Building added successfully", ["building_id" => $buildingId]);
        } catch (Exception $e) {
            self::response(false, "Failed to add building: " . $e->getMessage());
        }
    }

    public static function getAllBuildings($conn) {
        try {
            $result = $conn->query("SELECT id AS building_id, name AS building_name FROM buildings ORDER BY id DESC");
            $buildings = [];
            while ($row = $result->fetch_assoc()) $buildings[] = $row;
            self::response(true, "Buildings fetched successfully", $buildings);
        } catch (Exception $e) {
            self::response(false, "Failed to fetch buildings: " . $e->getMessage());
        }
    }

    public static function updateBuilding($conn, $building_id, $building_name) {
        if (empty($building_name)) self::response(false, "Building name cannot be empty");
        if (empty($building_id) || !is_numeric($building_id)) self::response(false, "Invalid building ID");

        try {
            $stmt = $conn->prepare("UPDATE buildings SET name = ? WHERE id = ?");
            $stmt->bind_param("si", $building_name, $building_id);
            if (!$stmt->execute()) throw new Exception($stmt->error);

            $stmt->close();
            self::response(true, "Building updated successfully", ["building_id" => $building_id, "building_name" => $building_name]);
        } catch (Exception $e) {
            self::response(false, "Failed to update building: " . $e->getMessage());
        }
    }

    public static function deleteBuilding($conn, $building_id) {
        if (empty($building_id) || !is_numeric($building_id)) {
            self::response(false, "Invalid building ID");
        }

        try {
            $stmt = $conn->prepare("DELETE FROM buildings WHERE id = ?");
            $stmt->bind_param("i", $building_id);
            if (!$stmt->execute()) throw new Exception($stmt->error);

            $stmt->close();
            self::response(true, "Building deleted successfully", ["building_id" => $building_id]);
        } catch (Exception $e) {
            self::response(false, "Failed to delete building: " . $e->getMessage());
        }
    }

    // ------------------- FLOOR -------------------
    public static function addFloor($conn, $building_id, $floor_name, $user_id) {
        if (empty($floor_name)) self::response(false, "Floor name cannot be empty");
        if (empty($building_id) || !is_numeric($building_id)) self::response(false, "Invalid building ID");

        try {
            $stmt = $conn->prepare("INSERT INTO floors (building_id, floor, created_by) VALUES (?, ?, ?)");
            $stmt->bind_param("isi", $building_id, $floor_name, $user_id);
            if (!$stmt->execute()) throw new Exception($stmt->error);

            $floorId = $stmt->insert_id;
            $stmt->close();

            self::response(true, "Floor added successfully", ["floor_id" => $floorId]);
        } catch (Exception $e) {
            self::response(false, "Failed to add floor: " . $e->getMessage());
        }
    }

    public static function getFloors($conn, $buildingId) {
        $query = "SELECT * FROM floors WHERE building_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $buildingId);
        $stmt->execute();
        $result = $stmt->get_result();
        $floors = $result->fetch_all(MYSQLI_ASSOC);

        if ($floors) {
            echo json_encode([
                'status' => true,
                'data' => $floors
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'message' => 'No floors found'
            ]);
        }
    }

    
    public static function updateFloor($conn, $floor_id, $floor_name) {
        if (empty($floor_name)) self::response(false, "Floor name cannot be empty");
        if (empty($floor_id) || !is_numeric($floor_id)) self::response(false, "Invalid floor ID");

        try {
            $stmt = $conn->prepare("UPDATE floors SET floor = ? WHERE id = ?");
            $stmt->bind_param("si", $floor_name, $floor_id);
            if (!$stmt->execute()) throw new Exception($stmt->error);

            $stmt->close();
            self::response(true, "Floor updated successfully", ["floor_id" => $floor_id, "floor_name" => $floor_name]);
        } catch (Exception $e) {
            self::response(false, "Failed to update floor: " . $e->getMessage());
        }
    }

    public static function deleteFloor($conn, $floor_id) {
        if (empty($floor_id) || !is_numeric($floor_id)) {
            self::response(false, "Invalid floor ID");
        }

        try {
            // Delete the floor from the database
            $stmt = $conn->prepare("DELETE FROM floors WHERE id = ?");
            $stmt->bind_param("i", $floor_id);
            if (!$stmt->execute()) throw new Exception($stmt->error);

            $stmt->close();

            self::response(true, "Floor deleted successfully", ["floor_id" => $floor_id]);
        } catch (Exception $e) {
            self::response(false, "Failed to delete floor: " . $e->getMessage());
        }
    }

    // ------------------- ROOM -------------------
    public static function addRoom($conn, $building_id, $floor_id, $room_name, $user_id) {
        if (empty($room_name)) self::response(false, "Room name cannot be empty");
        if (empty($building_id) || !is_numeric($building_id)) self::response(false, "Invalid building ID");

        try {
            $stmt = $conn->prepare("INSERT INTO rooms (building_id, floor_id, room, created_by) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iisi", $building_id, $floor_id, $room_name, $user_id);
            if (!$stmt->execute()) throw new Exception($stmt->error);

            $roomId = $stmt->insert_id;
            $stmt->close();

            self::response(true, "Room added successfully", ["room_id" => $roomId]);
        } catch (Exception $e) {
            self::response(false, "Failed to add room: " . $e->getMessage());
        }
    }

    public static function getAllBuildingFloorsRooms($conn) {
        try {
            $sql = "
                SELECT 
                    b.id AS building_id, b.name AS building_name,
                    f.id AS floor_id, f.floor AS floor_name,
                    r.id AS room_id, r.room AS room_name
                FROM buildings b
                LEFT JOIN floors f ON b.id = f.building_id
                LEFT JOIN rooms r ON b.id = r.building_id AND (f.id IS NULL OR r.floor_id = f.id)
                ORDER BY b.id ASC, f.id ASC, r.id ASC
            ";

            $result = $conn->query($sql);
            $data = [];

            while ($row = $result->fetch_assoc()) {
                $bId = $row['building_id'];
                $fId = $row['floor_id'];

                if (!isset($data[$bId])) {
                    $data[$bId] = [
                        'building_id' => $bId,
                        'building_name' => $row['building_name'],
                        'floors' => []
                    ];
                }

                if ($fId) {
                    if (!isset($data[$bId]['floors'][$fId])) {
                        $data[$bId]['floors'][$fId] = [
                            'floor_id' => $fId,
                            'floor_name' => $row['floor_name'],
                            'rooms' => []
                        ];
                    }

                    if ($row['room_id']) {
                        $data[$bId]['floors'][$fId]['rooms'][] = [
                            'room_id' => $row['room_id'],
                            'room_name' => $row['room_name']
                        ];
                    }
                }
            }

            // Re-index floors and rooms
            foreach ($data as &$b) {
                $b['floors'] = array_values($b['floors']);
                foreach ($b['floors'] as &$f) {
                    $f['rooms'] = array_values($f['rooms']);
                }
            }

            self::response(true, "Buildings, floors, and rooms fetched successfully", array_values($data));
        } catch (Exception $e) {
            self::response(false, "Failed to fetch data: " . $e->getMessage());
        }
    }

    public static function updateRoom($conn, $room_id, $room_name) {
        if (empty($room_name)) self::response(false, "Room name cannot be empty");
        if (empty($room_id) || !is_numeric($room_id)) self::response(false, "Invalid room ID");

        try {
            $stmt = $conn->prepare("UPDATE rooms SET room = ? WHERE id = ?");
            $stmt->bind_param("si", $room_name, $room_id);
            if (!$stmt->execute()) throw new Exception($stmt->error);

            $stmt->close();
            self::response(true, "Room updated successfully", ["room_id" => $room_id, "room_name" => $room_name]);
        } catch (Exception $e) {
            self::response(false, "Failed to update room: " . $e->getMessage());
        }
    }

    public static function deleteRoom($conn, $room_id) {
        if (empty($room_id) || !is_numeric($room_id)) {
            self::response(false, "Invalid room ID");
        }

        try {
            $stmt = $conn->prepare("DELETE FROM rooms WHERE id = ?");
            $stmt->bind_param("i", $room_id);
            if (!$stmt->execute()) throw new Exception($stmt->error);

            $stmt->close();

            self::response(true, "Room deleted successfully", ["room_id" => $room_id]);
        } catch (Exception $e) {
            self::response(false, "Failed to delete room: " . $e->getMessage());
        }
    }

    public static function getRooms($conn, $building_id, $floor_id) {
        if (empty($building_id) || !is_numeric($building_id)) {
            self::response(false, "Invalid building ID");
        }
        if (empty($floor_id) || !is_numeric($floor_id)) {
            self::response(false, "Invalid floor ID");
        }

        try {
            $stmt = $conn->prepare("SELECT id AS room_id, room AS room_name 
                                    FROM rooms 
                                    WHERE building_id = ? AND floor_id = ? 
                                    ORDER BY id ASC");
            
            $stmt->bind_param("ii", $building_id, $floor_id);
            $stmt->execute();
            $result = $stmt->get_result();

            $rooms = [];
            while ($row = $result->fetch_assoc()) {
                $rooms[] = $row;
            }

            $stmt->close();

            if (!empty($rooms)) {
                self::response(true, "Rooms fetched successfully", $rooms);
            } else {
                self::response(true, "No rooms found", []);
            }
        } catch (Exception $e) {
            self::response(false, "Failed to fetch rooms: " . $e->getMessage());
        }
    }

}
