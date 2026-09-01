-- =====================================================
-- MIGRAÇÃO DO BANCO DE DADOS - CRIANÇA FELIZ
-- =====================================================

CREATE DATABASE IF NOT EXISTS criancafeliz CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE criancafeliz;

-- =====================================================
-- TABELA: Usuario
-- =====================================================
CREATE TABLE IF NOT EXISTS Usuario (
  idusuario INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100),
  email VARCHAR(100) UNIQUE,
  Senha VARCHAR(100),
  nivel VARCHAR(50),
  status VARCHAR(20),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================================================
-- TABELA: Agenda
-- =====================================================
CREATE TABLE IF NOT EXISTS Agenda (
  id_notificacao INT AUTO_INCREMENT PRIMARY KEY,
  mensagem VARCHAR(255),
  tipo VARCHAR(50),
  lida BOOLEAN DEFAULT FALSE,
  data_envio DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================================================
-- TABELA: Log
-- =====================================================
CREATE TABLE IF NOT EXISTS Log (
  id_log INT AUTO_INCREMENT PRIMARY KEY,
  data_alteracao DATETIME DEFAULT CURRENT_TIMESTAMP,
  registro_alt VARCHAR(100),
  valor_anterior TEXT,
  valor_atual TEXT,
  acao VARCHAR(50),
  tabela_afetada VARCHAR(100),
  id_usuario INT,
  FOREIGN KEY (id_usuario) REFERENCES Usuario(idusuario)
    ON UPDATE CASCADE
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================================================
-- TABELA: Responsavel
-- =====================================================
CREATE TABLE IF NOT EXISTS Responsavel (
  idresponsavel INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100),
  cpf VARCHAR(14) UNIQUE,
  rg VARCHAR(20),
  telefone VARCHAR(15),
  email VARCHAR(100),
  parentesco VARCHAR(50),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================================================
-- TABELA: Atendido
-- =====================================================
CREATE TABLE IF NOT EXISTS Atendido (
  idatendido INT AUTO_INCREMENT PRIMARY KEY,
  status VARCHAR(20) DEFAULT 'Ativo',
  data_cadastro DATE,
  nome VARCHAR(100),
  data_nascimento DATE,
  faixa_etaria VARCHAR(20),
  cpf VARCHAR(14) UNIQUE,
  rg VARCHAR(20),
  endereco VARCHAR(255),
  numero VARCHAR(20),
  complemento VARCHAR(100),
  bairro VARCHAR(100),
  cidade VARCHAR(100),
  cep VARCHAR(10),
  foto VARCHAR(255),
  id_responsavel INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (id_responsavel) REFERENCES Responsavel(idresponsavel)
    ON UPDATE CASCADE
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================================================
-- TABELA: Ficha_Acolhimento
-- =====================================================
CREATE TABLE IF NOT EXISTS Ficha_Acolhimento (
  idficha_acolhimento INT AUTO_INCREMENT PRIMARY KEY,
  id_atendido INT UNIQUE,
  data_acolhimento DATE,
  encaminha_por VARCHAR(100),
  queixa_principal TEXT,
  escola VARCHAR(100),
  periodo VARCHAR(20),
  ponto_referencia VARCHAR(255),
  cras VARCHAR(100),
  ubs VARCHAR(100),
  cad_unico VARCHAR(20),
  acolhimento_responsavel VARCHAR(100),
  acolhimento_funcao VARCHAR(100),
  carimbo VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (id_atendido) REFERENCES Atendido(idatendido)
    ON UPDATE CASCADE
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================================================
-- TABELA: Encontro
-- =====================================================
CREATE TABLE IF NOT EXISTS Encontro (
  id_encontro INT AUTO_INCREMENT PRIMARY KEY,
  Dataencontro DATE,
  ID_usuario INT,
  evolucao TEXT,
  id_atendido INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (ID_usuario) REFERENCES Usuario(idusuario)
    ON UPDATE CASCADE
    ON DELETE SET NULL,
  FOREIGN KEY (id_atendido) REFERENCES Atendido(idatendido)
    ON UPDATE CASCADE
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================================================
-- TABELA: Documento
-- =====================================================
CREATE TABLE IF NOT EXISTS Documento (
  iddocumento INT AUTO_INCREMENT PRIMARY KEY,
  tipo VARCHAR(50),
  arquivo VARCHAR(255),
  data_upload DATETIME DEFAULT CURRENT_TIMESTAMP,
  IDatendido INT,
  FOREIGN KEY (IDatendido) REFERENCES Atendido(idatendido)
    ON UPDATE CASCADE
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================================================
-- TABELA: Ficha_Socioeconomico
-- =====================================================
CREATE TABLE IF NOT EXISTS Ficha_Socioeconomico (
  idficha INT AUTO_INCREMENT PRIMARY KEY,
  agua BOOLEAN,
  esgoto BOOLEAN,
  renda_familiar DECIMAL(10,2),
  energia BOOLEAN,
  qtd_pessoas INT,
  cond_residencia VARCHAR(100),
  moradia VARCHAR(100),
  nr_veiculos INT,
  observacoes TEXT,
  entrevistado VARCHAR(100),
  residencia VARCHAR(100),
  nr_comodos INT,
  construcao VARCHAR(100),
  id_atendido INT UNIQUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (id_atendido) REFERENCES Atendido(idatendido)
    ON UPDATE CASCADE
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================================================
-- TABELA: Familia
-- =====================================================
CREATE TABLE IF NOT EXISTS Familia (
  id_familia INT AUTO_INCREMENT PRIMARY KEY,
  id_ficha INT,
  nome VARCHAR(100),
  parentesco VARCHAR(50),
  data_nasc DATE,
  formacao VARCHAR(100),
  renda DECIMAL(10,2),
  FOREIGN KEY (id_ficha) REFERENCES Ficha_Socioeconomico(idficha)
    ON UPDATE CASCADE
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================================================
-- TABELA: Despesas
-- =====================================================
CREATE TABLE IF NOT EXISTS Despesas (
  id_despesa INT AUTO_INCREMENT PRIMARY KEY,
  valor_despesa DECIMAL(10,2),
  tipo_renda VARCHAR(50),
  valor_renda DECIMAL(10,2),
  id_ficha INT,
  FOREIGN KEY (id_ficha) REFERENCES Ficha_Socioeconomico(idficha)
    ON UPDATE CASCADE
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================================================
-- TRIGGERS DE LOG (Comentados - criar manualmente se necessário)
-- =====================================================

-- NOTA: Triggers devem ser criados via phpMyAdmin ou MySQL CLI
-- PDO não suporta DELIMITER

-- Para criar os triggers manualmente:
-- 1. Acesse phpMyAdmin
-- 2. Selecione o banco 'criancafeliz'
-- 3. Vá em SQL e execute cada trigger separadamente

/*
CREATE TRIGGER log_update_all
AFTER UPDATE ON Usuario
FOR EACH ROW
INSERT INTO Log (data_alteracao, registro_alt, valor_anterior, valor_atual, acao, tabela_afetada, id_usuario)
VALUES (
    NOW(),
    CONCAT('Campo alterado em Usuario.idusuario = ', OLD.idusuario),
    CONCAT('Nome antigo: ', OLD.nome, ', Email antigo: ', OLD.email),
    CONCAT('Nome novo: ', NEW.nome, ', Email novo: ', NEW.email),
    'UPDATE',
    'Usuario',
    @usuario_id
);

CREATE TRIGGER log_insert_all
AFTER INSERT ON Usuario
FOR EACH ROW
INSERT INTO Log (data_alteracao, registro_alt, valor_anterior, valor_atual, acao, tabela_afetada, id_usuario)
VALUES (
    NOW(),
    CONCAT('Novo registro em Usuario.idusuario = ', NEW.idusuario),
    NULL,
    CONCAT('Nome: ', NEW.nome, ', Email: ', NEW.email),
    'INSERT',
    'Usuario',
    @usuario_id
);

CREATE TRIGGER log_delete_all
AFTER DELETE ON Usuario
FOR EACH ROW
INSERT INTO Log (data_alteracao, registro_alt, valor_anterior, valor_atual, acao, tabela_afetada, id_usuario)
VALUES (
    NOW(),
    CONCAT('Removido Usuario.idusuario = ', OLD.idusuario),
    CONCAT('Nome: ', OLD.nome, ', Email: ', OLD.email),
    NULL,
    'DELETE',
    'Usuario',
    @usuario_id
);
*/

-- =====================================================
-- DADOS INICIAIS
-- =====================================================

-- Usuário admin padrão (senha: admin123)
INSERT INTO Usuario (nome, email, Senha, nivel, status) 
VALUES ('Administrador', 'admin@criancafeliz.org', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador', 'Ativo')
ON DUPLICATE KEY UPDATE nome = nome;

-- =====================================================
-- ÍNDICES PARA PERFORMANCE
-- =====================================================

CREATE INDEX idx_usuario_email ON Usuario(email);
CREATE INDEX idx_atendido_cpf ON Atendido(cpf);
CREATE INDEX idx_responsavel_cpf ON Responsavel(cpf);
CREATE INDEX idx_log_usuario ON Log(id_usuario);
CREATE INDEX idx_log_data ON Log(data_alteracao);
CREATE INDEX idx_encontro_atendido ON Encontro(id_atendido);
CREATE INDEX idx_documento_atendido ON Documento(IDatendido);

-- =====================================================
-- FIM DA MIGRAÇÃO
-- =====================================================
