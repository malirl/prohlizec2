<?php

class EmployeeModel
{
    public ?int $employee_id;
    public ?string $name;
    public ?string $surname;
    public ?string $job;
    public ?string $wage;
    public ?string $room;
    public ?string $login;
    public ?string $pswd;
    public ?int $admin;


    public array $validationErrors = [];

    public function getValidationErrors(): array
    {
        return $this->validationErrors;
    }


    public function __construct($params = array())
    {
        $this->employee_id = $params[employee_id] ?: null;
        $this->name = $params[name] ?: null;
        $this->surname = $params[surname] ?: null;
        $this->job = $params[job] ?: null;
        $this->wage = $params[wage] ?: null;
        $this->room = $params[room] ?: null;
        $this->login = $params[login] ?: null;
        $this->pswd = $params[pswd] ?: null;
        $this->admin = (int)$params[admin];
        $this->keys = $params[keys] ?: null;
        $this->params = $params;
    }

    public function hash($pswd)
    {
        return password_hash(
            $pswd, PASSWORD_BCRYPT,
            [
            'cost' => 10
            ]
        );
    }

    public function insert() : bool
    {
        try {
            $sql = "INSERT INTO employee (name, surname, job, wage, room, login, pswd, admin) VALUES (:name, :surname, :job, :wage, :room, :login, :pswd, :admin)";

            $stmt = DB::getConnection()->prepare($sql);
            $stmt->bindParam(':name', $this->name);
            $stmt->bindParam(':surname', $this->surname);
            $stmt->bindParam(':job', $this->job);
            $stmt->bindParam(':wage', $this->wage);
            $stmt->bindParam(':room', $this->room);
            $stmt->bindParam(':login', $this->login);

            $stmt->bindParam(
                ':pswd',
                $this->hash($this->pswd)
            );

            $stmt->bindParam(':admin', $this->admin, PDO::PARAM_INT);
            $stmt->execute();

            $sql = "SELECT employee_id FROM employee WHERE login = :login";
            $stmt = DB::getConnection()->prepare($sql);
            $stmt->bindParam(':login', $this->login);
            $stmt->execute();

            $this->employee_id =  $stmt->fetch()->employee_id;

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function update($pswdSave) : bool
    {
        try {
            $pswd = ($pswdSave) ? ", pswd=:pswd" : "";
            $sql = "UPDATE employee SET name=:name, surname=:surname, job=:job, wage=:wage, room=:room, login=:login, admin=:admin" . $pswd . " WHERE employee_id=:employee_id";
            $stmt = DB::getConnection()->prepare($sql);
            $stmt->bindParam(':employee_id', $this->employee_id);
            $stmt->bindParam(':name', $this->name);
            $stmt->bindParam(':surname', $this->surname);
            $stmt->bindParam(':job', $this->job);
            $stmt->bindParam(':wage', $this->wage);
            $stmt->bindParam(':room', $this->room);
            $stmt->bindParam(':login', $this->login);
            if ($pswdSave) {
                $stmt->bindParam(
                    ':pswd',
                    $this->hash($this->pswd)
                );
            }
            $stmt->bindParam(':admin', $this->admin, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (\Exception $e) {
            return false;
        }
    }

    public function exists($employee_id)
    {
        if (!is_numeric($employee_id)) {
            return false;
        }

        $stmt = DB::getConnection()->prepare("SELECT employee_id FROM employee WHERE employee_id=:employee_id");
        $stmt->bindParam(':employee_id', $employee_id);
        $stmt->execute();
        return (bool) $stmt->fetch();
    }


    public function getById($employee_id = null)
    {
        $employee_id = $employee_id ?: $this->employee_id;
        $stmt = DB::getConnection()->prepare("SELECT employee.name AS name, employee.surname AS surname, room, room.phone AS phone, employee.job AS job, employee_id, wage, login, admin FROM employee LEFT JOIN room ON employee.room = room.room_id WHERE employee_id=:employee_id");
        $stmt->bindParam(':employee_id', $employee_id);
        $stmt->execute();
        $record = $stmt->fetch();

        if (!$record) {
            return null;
        }

        return array(
        employee_id => $record->employee_id,
        name => $record->name,
        surname => $record->surname,
        job => $record->job,
        wage => $record->wage,
        room => $record->room,
        login => $record->login,
        admin => $record->admin
        );
    }

    public function getAll($orderBy = "name", $orderDir = "ASC") : PDOStatement
    {
        $stmt = DB::getConnection()->prepare("SELECT CONCAT(employee.surname, \" \", employee.name) AS name, room.name AS room, room.phone AS phone, employee.job AS job, employee_id FROM employee LEFT JOIN room ON employee.room = room.room_id ORDER BY ${orderBy} " . (($orderDir) ? "DESC" : "") . ", name");
        $stmt->execute();
        return $stmt;
    }

    public function deleteById(int $employeeId) : bool
    {
        $sql = "DELETE FROM employee WHERE employee_id=:employee_id";
        $stmt = DB::getConnection()->prepare($sql);
        $stmt->bindParam(':employee_id', $employeeId);
        return $stmt->execute();
    }

    public function delete() : bool
    {
        return self::deleteById($this->employee_id);
    }


    public function getFromPost() : self
    {
        return new self(
            array(
            employee_id => filter_input(INPUT_POST, "employee_id", FILTER_VALIDATE_INT),
            name => filter_input(INPUT_POST, "name"),
            surname => filter_input(INPUT_POST, "surname"),
            job => filter_input(INPUT_POST, "job"),
            wage => filter_input(INPUT_POST, "wage"),
            room => filter_input(INPUT_POST, "room"),
            login => filter_input(INPUT_POST, "login"),
            pswd => filter_input(INPUT_POST, "pswd"),
            admin => (int)filter_input(INPUT_POST, "user"),
            keys => $_POST[roomsForKeys] ?: null
            )
        );
    }

    public function validate() : bool
    {
        $errors = [];
        $isOk = true;

        $notEmpty = [
        "name","surname","job","wage","room","login"
        ];

        $numsOnly = [
          "wage","room"
        ];

        foreach ($notEmpty as $key => $value) {
            if (!$this->$value) {
                $isOk = false;
                $errors[$value] = "Nesmí být prázdný!";
            }
        }

        foreach ($numsOnly as $key => $value) {
            if (!filter_var($this->$value, FILTER_VALIDATE_INT)) {
                $errors[$value] = $errors[$value] ?: $this->$value . " není číslo!";
                $this->params[$value] = null;
                $isOk = false;
            }
        }

        if (array_filter($this->keys, "is_numeric") !== $this->keys) {
            $isOk = false;
        }

        if ($this->admin === null) {
            $isOk = false;
        }

        


        $this->validationErrors = $errors;
        return $isOk;
    }
}
