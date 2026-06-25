# Café sem Fronteiras

Sistema web de jogo de atendimento em restaurante, desenvolvido como projeto da disciplina de Programação Web 1.

O jogador assume o papel de um garçom e deve anotar corretamente os pedidos dos clientes, entregá-los na cozinha e manter as estrelas do restaurante antes que o tempo acabe. A cada nível, o cenário muda para um país diferente (Brasil, França, Japão ou Estados Unidos), aumentando o desafio progressivamente. O jogo conta com sistema de pontuação, ranking e ligas competitivas entre jogadores.

---

## Tecnologias utilizadas

- **Frontend:** HTML, CSS e JavaScript
- **Backend:** PHP
- **Banco de dados:** MySQL (via MySQLi)
- **Controle de versão:** Git / GitHub

---

## Funcionalidades

- Cadastro e autenticação de usuários com senha criptografada (`password_hash`)
- Edição de nome, e-mail e senha do perfil
- Jogo de atendimento com movimentação por teclado (WASD ou setas)
- 4 cenários temáticos com fundos, mesas e decorações exclusivas
- 4 personagens de clientes com sprites estáticos
- 1 personagem de atendente com sprites animados
- Sistema de estrelas do restaurante e game over
- HUD com nível, pontuação, pedidos e estrelas em tempo real
- Tela de pausa, reinício de nível e saída do jogo
- Histórico de partidas e relatório de desempenho
- Sistema de ligas com criação, entrada por palavra-chave e ranking

---

## Estrutura do projeto

```
PW1/
├── front-end/
│   ├── index.php                  # Página inicial
│   ├── components/
│   │   └── menu.php               # Componente de navegação
│   ├── pages/
│   │   ├── game.php               # Tela do jogo
│   │   ├── liga.php               # Sistema de ligas
│   │   ├── historicoRelatorio.php # Histórico de partidas
│   │   ├── score.php              # Pontuação
│   │   └── tutorial.php           # Tutorial
│   ├── scripts/
│   │   ├── game.js                # Lógica principal do jogo
│   │   └── pages-script.js        # Scripts das páginas
│   ├── data/
│   │   ├── cenarios.js            # Dados dos cenários (países)
│   │   ├── personagens.js         # Catálogo de personagens
│   │   └── game-objects.js        # Falas, pedidos e avaliações
│   ├── css/
│   │   ├── pages.css              # Estilos globais e páginas
│   │   ├── game.css               # Estilos do jogo
│   │   ├── liga.css               # Estilos das ligas
│   │   ├── auth.css               # Estilos de login/cadastro
│   │   └── historicoRelatorio.css # Estilos do histórico
│   └── assets/
│       └── images/                # Imagens de personagens, cenários e fundos
│
├── back-end/
│   ├── banco/
│   │   ├── config.php             # Configurações do banco
│   │   ├── conexao.php            # Conexão com o MySQL
│   │   ├── database.php           # Utilitários de banco
│   │   └── inicializar/
│   │       └── tables.php         # Criação das tabelas
│   ├── login/
│   │   ├── login.php              # Autenticação
│   │   ├── cadastrar.php          # Cadastro de usuário
│   │   ├── logout.php             # Encerramento de sessão
│   │   ├── editar.php             # Roteamento de edição
│   │   ├── editar-nome-email.php  # Edição de nome e e-mail
│   │   └── editar-senha.php       # Edição de senha
│   └── liga/
│       └── liga-oficial.php       # Lógica da liga oficial
│
├── AI_USAGE_LOG.md                # Registro de uso de IA generativa
└── README.md
```

---

## Banco de dados

O banco possui 4 tabelas principais:

| Tabela | Descrição |
|---|---|
| `usuarios` | Armazena nome, e-mail e senha dos jogadores |
| `partida` | Registra pontuação e nível de cada sessão de jogo |
| `liga` | Ligas criadas pelos usuários, com nome, palavra-chave e data de encerramento |
| `ligaUsuario` | Relacionamento entre jogadores e ligas (N para N) |

Para criar as tabelas, acesse `back-end/banco/inicializar/tables.php` pelo navegador após configurar o banco.

---

## Como executar o projeto

### Pré-requisitos

- PHP 7.4 ou superior
- MySQL 5.7 ou superior
- Servidor web (Apache/Nginx) ou XAMPP/WAMP

### Instalação

1. Clone o repositório:

```bash
git clone https://github.com/Mrcff/PW1.git
```

2. Entre na pasta do projeto:

```bash
cd PW1
```

3. Configure o banco de dados em `back-end/banco/config.php` com suas credenciais.

4. Inicialize as tabelas acessando pelo navegador:

```
http://localhost/PW1/back-end/banco/inicializar/tables.php
```

5. Acesse a aplicação:

```
http://localhost/PW1/front-end/index.php
```

---

## Como jogar

| Tecla | Ação |
|---|---|
| `W` / `↑` | Mover para cima |
| `S` / `↓` | Mover para baixo |
| `A` / `←` | Mover para a esquerda |
| `D` / `→` | Mover para a direita |
| `Espaço` | Interagir com mesa ou cozinha |
| `Esc` | Pausar / Cancelar ação |

**Objetivo:** aproxime-se das mesas, anote o pedido corretamente e entregue na cozinha antes que a paciência dos clientes acabe. Não deixe as 5 estrelas do restaurante chegarem a zero!

---

## Fluxo do jogo

```
Início → Escolher personagem → Cenário sorteado → Atender mesas → Entregar pedidos → Passar de nível → (repete até game over)
```

---

## Como contribuir com o projeto

Antes de começar a programar, atualize seu repositório local:

```bash
git pull
```

Após suas alterações:

```bash
git add .
git commit -m "tipo: descrição"
git push
```

### Padrão de commits

| Tag | Descrição |
|---|---|
| `feat` | Nova funcionalidade |
| `fix` | Correção de erro |
| `style` | Alterações visuais |
| `refactor` | Reorganização do código |
| `docs` | Documentação |
| `test` | Testes |

### Boas práticas da equipe

- Sempre fazer `git pull` antes de começar
- Não alterar código de outros integrantes sem avisar
- Fazer commits organizados e descritivos