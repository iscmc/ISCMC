<?php
//arquivo de funções de banco de dados
if (!isset($_SESSION)) {
	session_start();
}

//abre a conexão
function open_database() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME;
        $conn = new PDO($dsn, DB_USER, DB_PASSWORD);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		
		return $conn;
    } catch (PDOException $e) {
        echo $e->getMessage();
        return null;
	}
}
//fecha a conexão passada
function close_database($conn) {
    $conn = null;
}

//Pesquisa um Registro pelo ID em uma Tabela
function find($table = null, $id = null) {

	$database = open_database();
	$found = null;

	try {
		if ($id) {
			//identifica um único registro pelo ID (chave primaria)
			$stmt = $database->prepare("SELECT * FROM $table WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $found = $stmt->fetch(PDO::FETCH_ASSOC);
		} else {
			//se não for passado o ID ele retornará todos os registros
			$sql = "SELECT * FROM " . $table; //identifica o conjunto de todos os registros
			$result = $database->query($sql);
			
			$stmt = $database->prepare("SELECT * FROM $table");
            $stmt->execute();
            $found = $stmt->fetchAll(PDO::FETCH_ASSOC);
		}
	} catch (PDOException $e) {
        $_SESSION['message'] = $e->getMessage();
        $_SESSION['type'] = 'danger';
	}

	close_database($database);
    return $found;
}

//Pesquisa Todos os Registros de uma Tabela
function find_all($table) {
	//forma mais prática de chamar a função sem precisar de parâmetros
	return find($table);
}

//insere um registro no banco
function save($table = null, $data = null) {

	$database = open_database();
	
    $columns = implode(', ', array_keys($data));
    $placeholders = ':' . implode(', :', array_keys($data));

    $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
    $stmt = $database->prepare($sql);

	try {
        foreach ($data as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->execute();
        $_SESSION['message'] = 'Registro cadastrado com sucesso';
        $_SESSION['type'] = 'success';
    } catch (PDOException $e) {
        $_SESSION['message'] = 'Não foi possível realizar a operação';
        $_SESSION['type'] = 'danger';
    }

	close_database($database);
}


// atualiza um registro em uma tabela, por ID
function update($table = null, $id = 0, $data = null) {
	$database = open_database();

	$set = '';
    foreach ($data as $key => $value) {
        $set .= "$key = :$key, ";
    }
    $set = rtrim($set, ', '); //remove a última vírgula

	$sql = "UPDATE $table SET $set WHERE id = :id";
    $stmt = $database->prepare($sql);
	
	try {
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        foreach ($data as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->execute();
        $_SESSION['message'] = 'Registro atualizado com sucesso';
        $_SESSION['type'] = 'success';
    } catch (PDOException $e) {
        $_SESSION['message'] = 'Não foi possível realizar a operação';
        $_SESSION['type'] = 'danger';
    }

    close_database($database);
}

// Remove uma linha de uma tabela pelo ID do registro
function remove($table = null, $id = null) {
	$database = open_database();

	try {
		if ($id) {
			$sql = "DELETE FROM $table WHERE id = :id";
			
			$stmt = $database->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
			$_SESSION['message'] = "Registro Removido com Sucesso.";
            $_SESSION['type'] = 'success';
		}
	} catch (PDOException $e) {
		$_SESSION['message'] = $e->GetMessage();
		$_SESSION['type'] = 'danger';
	}
	close_database($database);
}