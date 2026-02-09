# 🎴 Oracle TGC

Sistema completo para visualização, identificação e gerenciamento de cartas colecionáveis (Magic: The Gathering, Pokémon TCG, One Piece).

---

## 🛠️ Tecnologias

### Backend
- **PHP 8.2** - Linguagem
- **Symfony 6.4** - Framework
- **MySQL 8** - Banco de dados
- **Doctrine ORM** - Mapeamento objeto-relacional
- **Doctrine Migrations** - Gerenciamento de schema

### Frontend
- **React 18** - Biblioteca UI
- **TypeScript** - Tipagem estática
- **Vite** - Build tool
- **Tailwind CSS** - Estilização
- **Shadcn/ui** - Componentes UI

---

## 📋 Funcionalidades

### ✅ Implementado
- 🔍 Busca de cartas (Magic, Pokémon)
- 📸 OCR para identificação de cartas
- 💰 Conversão de moedas (USD, BRL, BTC)
- 🎨 Interface moderna e responsiva

### 🚧 Em Desenvolvimento
- 📦 Sistema de inventário
- 🗂️ Coleções e decks
- 👤 Autenticação de usuários
- 💾 Sincronização com APIs externas
- 📊 Dashboard de estatísticas

---

## 🗄️ Banco de Dados

### MySQL

A API usa **MySQL 8** como banco de dados.

**Configuração padrão (local / Homestead):**
- Host: `127.0.0.1` (ou `localhost`)
- Porta: `3306` (no Homestead, do host use `33060`)
- Database: `oracle_tgc`
- Usuário: `oracle_tgc` (local) ou `homestead` (Homestead)
- Senha: `oracle_tgc` (local) ou `secret` (Homestead)

### Migrações

```bash
# Na pasta Api.OracleTGC (local ou dentro da VM Homestead)
php bin/console doctrine:migrations:migrate

# Criar nova migração
php bin/console doctrine:migrations:generate
```

---

## 🖥️ Desenvolvimento com Homestead (VirtualBox) — rodar a partir de ~/Homestead

O projeto é configurado para rodar **sempre a partir da pasta ~/Homestead**:

1. **Configurar uma vez:** na raiz do OracleTGC, execute `./setup-homestead.sh` (copia `Homestead.yaml` para `~/Homestead`). Ou copie manualmente: `cp Homestead.yaml ~/Homestead/Homestead.yaml` e ajuste `folders.map` no destino.
2. Adicione ao `/etc/hosts`: `192.168.56.56 api.oracle-tgc.test` e `192.168.56.56 oracle-tgc.test`.
3. **Subir a VM:** `cd ~/Homestead && vagrant up` e depois `vagrant ssh`.

Resumo: **API** em `https://api.oracle-tgc.test`, **frontend** em `https://oracle-tgc.test`, banco **oracle_tgc** no MySQL do Homestead (credenciais: `homestead` / `secret`). Guia completo: **[HOMESTEAD_GUIDE.md](./HOMESTEAD_GUIDE.md)**.

---

## 🔧 Desenvolvimento Local

### Backend

```bash
cd Api.OracleTGC

# Instalar dependências
composer install

# Configurar banco de dados
# Editar config.php ou variáveis de ambiente

# Iniciar servidor
php -S localhost:8000 -t public
```

### Frontend

```bash
cd Web.OracleTGC

# Instalar dependências
npm install

# Iniciar servidor de desenvolvimento
npm run dev
```

---

## 📚 Documentação

- **[HOMESTEAD_GUIDE.md](./HOMESTEAD_GUIDE.md)** - Guia Homestead (VirtualBox)
- **[Api.OracleTGC/DEV_GUIDE.md](./Api.OracleTGC/DEV_GUIDE.md)** - Guia de desenvolvimento
- **[Api.OracleTGC/SYNC_STRATEGY.md](./Api.OracleTGC/SYNC_STRATEGY.md)** - Estratégia de sincronização
- **[Api.OracleTGC/docs/](./Api.OracleTGC/docs/)** - Documentação da API

---

## 🐛 Troubleshooting

### Problemas Comuns

**Porta já em uso:** use outra porta ao iniciar (ex.: `php -S localhost:8001 -t public` ou altere a porta no Vite).

**Banco de dados não conecta:**
- Local: verifique se o MySQL está rodando (`sudo systemctl status mysql`).
- Homestead: veja [HOMESTEAD_GUIDE.md](./HOMESTEAD_GUIDE.md) e confira `DATABASE_URL` no `.env`.

**Erro de permissões:**
```bash
sudo chown -R $USER:$USER Api.OracleTGC/var
chmod -R 775 Api.OracleTGC/var
```

📖 **Mais soluções**: [HOMESTEAD_GUIDE.md](./HOMESTEAD_GUIDE.md#10-troubleshooting)

---

## 🔐 Variáveis de Ambiente

Principais variáveis (veja `.env` ou `.env.homestead.example` na API):

| Variável | Descrição | Padrão (local / Homestead) |
|----------|-----------|----------------------------|
| `APP_ENV` | Ambiente | `dev` |
| `APP_SECRET` | Chave secreta | - |
| `DATABASE_URL` | DSN MySQL | `mysql://oracle_tgc:oracle_tgc@127.0.0.1:3306/oracle_tgc` ou `mysql://homestead:secret@127.0.0.1:3306/oracle_tgc` |

---

## 📝 Comandos Úteis

```bash
# Homestead: subir VM, entrar, reprovisionar
cd ~/Homestead && vagrant up
vagrant ssh
vagrant reload --provision

# API: migrações e cache
cd Api.OracleTGC && php bin/console doctrine:migrations:migrate
php bin/console cache:clear

# Frontend: dev ou build
cd Web.OracleTGC && npm run dev
npm run build
```

---

## 🤝 Contribuindo

1. Fork o projeto
2. Crie uma branch (`git checkout -b feature/nova-funcionalidade`)
3. Commit suas mudanças (`git commit -m 'Adiciona nova funcionalidade'`)
4. Push para a branch (`git push origin feature/nova-funcionalidade`)
5. Abra um Pull Request

---

## 📄 Licença

Este projeto está sob a licença MIT. Veja o arquivo `LICENSE` para mais detalhes.

---

## 🔗 Links Úteis

- [Documentação Symfony](https://symfony.com/doc/6.4/)
- [Documentação React](https://react.dev/)
- [Documentação MySQL](https://dev.mysql.com/doc/)
- [Laravel Homestead](https://laravel.com/docs/homestead)

---

