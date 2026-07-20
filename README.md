# Lesarge Workforce Hub

A comprehensive HR CRM and workforce management platform designed to improve current WordPress implementation and migrate to a scalable, standalone MVC architecture.

## 🎯 Features

- **HR CRM** — Employee profiles, contact management, organizational structure
- **Candidate Management** — Applicant tracking, resume parsing, interview scheduling
- **Recruitment** — Job postings, pipeline management, offer workflows
- **Employee Management** — Personnel records, skills, certifications, history
- **Time Tracking** — Clock in/out, attendance, overtime management
- **Payroll** — Salary processing, deductions, reports, tax compliance
- **Documents** — Contract management, policy library, eSignature support
- **AI Assistant** — Intelligent automation, recommendations, reporting
- **Mobile API** — REST endpoints for mobile applications

## 🛠 Tech Stack

| Component | Version |
|-----------|---------|
| **Language** | PHP 8.2+ |
| **Framework** | WordPress (current) → Custom MVC (target) |
| **Database** | MariaDB 10.5+ |
| **Frontend** | Bootstrap, JavaScript |
| **API** | REST v2 |

## 🏗 Architecture

**Current:** WordPress multisite (subdomain-based network)  
**Target:** Standalone MVC with modular components  
**Strategy:** Incremental migration while maintaining backward compatibility

```
lesarge.ch/              # Main site
app.lesarge.ch/          # Application dashboard
admin.lesarge.ch/        # Admin panel
```

## 🚀 Quick Start

### Prerequisites

- PHP 8.2+
- MariaDB 10.5+
- Composer

### Local Development Setup

1. **Clone repository**
   ```bash
   git clone https://github.com/lesarge/lesarge-workforce-hub.git
   cd lesarge-workforce-hub
   ```

2. **Create .env file**
   ```bash
   cp .env.example .env
   # Edit .env with local database credentials
   ```

3. **Create database**
   ```bash
   mysql -u root -p
   > CREATE DATABASE lesarge_workforce_hub CHARACTER SET utf8mb4;
   > exit
   ```

4. **Install WordPress**
   ```bash
   wp core install --url=localhost:8080 --title="Lesarge Hub" \
     --admin_user=admin --admin_email=admin@lesarge.local
   ```

5. **Install dependencies**
   ```bash
   composer install
   ```

6. **Start server**
   ```bash
   php -S localhost:8080
   ```

Visit `http://localhost:8080` in your browser.

## 📁 Project Structure

```
lesarge-workforce-hub/
├── app/                          # MVC application
│   ├── Core/                     # Framework base classes
│   │   ├── Controller.php        # Base controller
│   │   ├── Model.php             # Base model
│   │   └── Response.php          # HTTP response
│   ├── Modules/                  # Application modules
│   │   ├── HrCrm/
│   │   ├── Recruitment/
│   │   ├── Payroll/
│   │   └── ...
│   └── Views/                    # View templates
├── public/                        # Web root
├── storage/                       # Files, logs
├── tests/                         # Unit & integration tests
├── config/                        # Configuration files
├── wp-config.php                 # WordPress config (secure)
├── .env                          # Environment variables (local only)
├── .env.example                  # Environment template
└── README.md                     # This file
```

## 🔒 Security

**All credentials use environment variables** — Never hardcoded in code.

1. **Create .env file from template**
   ```bash
   cp .env.example .env
   ```

2. **Generate security salts**
   ```
   https://api.wordpress.org/secret-key/1.1/salt/
   ```

3. **Update .env with credentials**
   ```env
   DB_PASSWORD=your_secure_password
   AUTH_KEY=generated_key
   # ... etc
   ```

See `docs/SECURITY.md` for security audit and best practices.

## 🔄 Development Guidelines

- **Security:** Prepared SQL statements, never interpolate user input
- **Modularity:** Create isolated, reusable components
- **Compatibility:** Never delete existing functionality
- **Documentation:** All production code must be documented
- **Testing:** Write tests for new features

## 📚 Documentation

- **[SECURITY.md](docs/SECURITY.md)** — Security audit & remediation
- **[MIGRATION-ROADMAP.md](docs/MIGRATION-ROADMAP.md)** — MVC migration strategy
- **[Copilot Instructions](.github/copilot-instructions.md)** — Development guidelines

## 📋 Next Steps

1. ✅ **Fixed security issues** — Credentials now via environment variables
2. 🔐 **Generate security salts** — Create unique keys for your environment
3. 📝 **Create .env file** — Copy template and add credentials
4. 🏗️ **Set up MVC structure** — Base classes ready for module development
5. 🧪 **Add tests** — PHPUnit test framework
6. 🚀 **Build first module** — HR CRM module

## 🆘 Troubleshooting

**Database connection error?**
- Check .env credentials match your database
- Verify database exists: `mysql -u root -e "SHOW DATABASES;"`

**WordPress won't load?**
- Verify .env file exists and has all required variables
- Check PHP version: `php -v` (need 8.2+)

**Permission denied on wp-content?**
- Run: `chmod -R 755 wp-content/`

## 📞 Support

- **Issues:** [GitHub Issues](https://github.com/lesarge/lesarge-workforce-hub/issues)
- **Docs:** Check `docs/` directory
- **Guidelines:** See `.github/copilot-instructions.md`

## 📄 License

Apache License 2.0 — See [LICENSE](LICENSE)

---

**Status:** Early Development | **Version:** 0.1.0 | **Last Updated:** 2026-07-20
