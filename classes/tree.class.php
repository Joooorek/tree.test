<?php 

    class Tree 
    {

        private PDO $pdo;

        public function __construct() 
        {
            try {
                $this->pdo = new PDO("mysql:host=MySQL-5.7;dbname=tree;charset=utf8mb4", "root", "mysql", [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]);
            } catch (PDOException $e) {
                die("[Error] Cannot establish connection with DataBase. Reason: " . $e->getMessage());
            }
        }

        public function Get(int $parent_id = null, int $level = 0): array 
        {
            $tree = [];

            $query = $this->pdo->prepare("SELECT * FROM tree WHERE parent_id " . ($parent_id == null ? "IS NULL" : "= :parent_id") . " ORDER BY id");
            if ($parent_id != null) $query->bindValue(":parent_id", $parent_id, PDO::PARAM_INT);
            $query->execute();

            $elements = $query->fetchAll();
            foreach ($elements as $element) {
                $children = $this->Get((int)$element["id"], $level + 1);

                $tree[] = [
                    "id" => $element["id"],
                    "name" => str_repeat("&nbsp", $level) . ($element["parent_id"] == NULL ? "⬝" : "⮡ ") . " " . $element["name"],
                    "children" => $children
                ];
            }

            return $tree;
        }

        public function GetPlain(int $parent_id = null, int $level = 0): array 
        {
            $tree = [];

            $query = $this->pdo->prepare("SELECT * FROM tree WHERE parent_id " . ($parent_id == null ? "IS NULL" : "= :parent_id") . " ORDER BY id");
            if ($parent_id != null) $query->bindValue(":parent_id", $parent_id, PDO::PARAM_INT);
            $query->execute();

            $elements = $query->fetchAll();
            foreach ($elements as $element) {
                $tree[] = [
                    "id" => $element["id"],
                    "name" => str_repeat("-", $level) . " " . $element["name"],
                    "parent" => $element["parent_id"]
                ];

                $tree = array_merge($tree, $this->GetPlain((int)$element["id"], $level + 1));
            }

            return $tree;
        }

        public function AddElement(string $name, ?int $parent_id = null): bool 
        {
            $query = $this->pdo->prepare("INSERT INTO tree (name, parent_id) VALUES (:name, :parent_id)");

            return $query->execute([":name" => $name, "parent_id" => $parent_id]);
        }

        public function DeleteElement(int $id): bool 
        {
            $query = $this->pdo->prepare("DELETE FROM tree WHERE id = :id");

            return $query->execute([":id" => $id]);
        }

        public function Draw(array $elements, int $level = 0): void 
        {
            foreach ($elements as $element) {
                echo($element["name"] . "<br>");

                if (!empty($element["children"])) {
                    $this->Draw($element["children"], $level + 1);
                }
            }
        }

    }

?>