# 🎲 Sistema de Loterias — Ambiente Docker com PHP & MySQL

Este projeto fornece um ambiente completo para executar aplicações PHP com MySQL utilizando **Docker**, 
facilitando o desenvolvimento, testes e deploy local.

## 📌 Tecnologias Utilizadas
- **PHP + Apache**
- **MySQL**
- **phpMyAdmin**
- **Docker & Docker Compose**

## 📁 Estrutura do Projeto

loterias/
├── docker/ # Configurações customizadas do Docker
│ └── php/ # Arquivos relacionados ao container PHP
├── docker-compose.yml # Serviços do Docker
├── index.php # Página inicial do sistema
├── LICENSE # Licença do projeto
└── README.md # Documentação

## 🐳 Como subir o ambiente

### 1️⃣ Abrir o terminal na pasta do projeto
Entre na pasta `loterias`:

```bash
cd loterias

2️⃣ Subir o ambiente

Inicie todos os serviços:

docker compose up -d

3️⃣ Encerrar os serviços

Caso queira parar tudo:

docker compose down

🌐 Acessar os serviços

| Serviço                     | URL / Porta                                    |
| --------------------------- | ---------------------------------------------- |
| **Apache + PHP**            | [http://localhost:8080](http://localhost:8080) |
| **phpMyAdmin**              | [http://localhost:8081](http://localhost:8081) |
| **MySQL (container)**       | host: `mysql` — porta: **3306**                |
| **MySQL (Windows / local)** | porta: **3307**                                |

🗄️ Banco de Dados

O projeto utiliza dois acessos MySQL:

🔸 MySQL interno do Docker

Use em conexões internas (por exemplo, no PHP):

host: mysql
port: 3306
user: root
password: <definida no docker-compose>

🔸 MySQL local (Windows)

Acesso externo via ferramentas locais:
localhost:3307

🚀 Desenvolvimento

O arquivo principal da aplicação é:
index.php

Ele fica acessível via navegador em:

👉 http://localhost:8080

Modificações feitas no código são refletidas imediatamente, pois o Docker está configurado com mapeamento de volumes.

🧹 Comandos úteis

docker compose logs -f php
docker compose restart php

Acessar o MySQL dentro do container:
docker exec -it mysql bash
mysql -u root -p

📝 Licença

Este projeto está licenciado sob os termos da MIT License.