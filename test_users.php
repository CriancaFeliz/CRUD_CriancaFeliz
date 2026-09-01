<?php
// Teste simples para verificar se as classes estão sendo carregadas
require_once 'bootstrap.php';

echo "Bootstrap carregado...<br>";

try {
    echo "Tentando carregar BaseModel...<br>";
    $baseModel = new BaseModel('test.json');
    echo "BaseModel carregado com sucesso!<br>";
    
    echo "Tentando carregar User...<br>";
    $user = new User();
    echo "User carregado com sucesso!<br>";
    
    echo "Tentando carregar AuthService...<br>";
    $authService = new AuthService();
    echo "AuthService carregado com sucesso!<br>";
    
    echo "Tentando carregar BaseController...<br>";
    $baseController = new BaseController();
    echo "BaseController carregado com sucesso!<br>";
    
    echo "Tentando carregar UserService...<br>";
    $userService = new UserService();
    echo "UserService carregado com sucesso!<br>";
    
    echo "Tentando carregar UserController...<br>";
    $userController = new UserController();
    echo "UserController carregado com sucesso!<br>";
    
    echo "<br><strong>Todos os componentes foram carregados com sucesso!</strong>";
    
} catch (Exception $e) {
    echo "<strong>Erro encontrado:</strong> " . $e->getMessage() . "<br>";
    echo "<strong>Arquivo:</strong> " . $e->getFile() . "<br>";
    echo "<strong>Linha:</strong> " . $e->getLine() . "<br>";
}
?>
