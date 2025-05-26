# PostgreSQL Database Setup for Vercel Deployment

This guide will help you set up your Supabase PostgreSQL database correctly for your Perpustakaan Muflih application deployment on Vercel.

## Requirements

1. A Supabase account (https://supabase.com)
2. A Vercel account (https://vercel.com)
3. Your Perpustakaan Muflih project code

## Database Setup Steps

### 1. Create a Supabase Project

1. Log in to your Supabase account
2. Create a new project 
3. Give it a name (e.g., `perpustakaan-muflih`)
4. Note your database password - you'll need it later

### 2. Create Required Tables

Connect to your Supabase SQL Editor and run the following SQL commands:

```sql
-- Membuat tipe ENUM terlebih dahulu
CREATE TYPE role_enum AS ENUM ('admin', 'user');
CREATE TYPE status_peminjaman_enum AS ENUM ('dipinjam', 'dikembalikan');

-- Tabel users
CREATE TABLE users (
  id SERIAL PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  email VARCHAR(100) UNIQUE,
  role role_enum NOT NULL DEFAULT 'user'
);

-- Tabel buku
CREATE TABLE buku (
  id SERIAL PRIMARY KEY,
  judul VARCHAR(255) NOT NULL,
  pengarang VARCHAR(255) NOT NULL,
  penerbit VARCHAR(255) NOT NULL,
  tahun_terbit SMALLINT NOT NULL,
  genre VARCHAR(100) NOT NULL,
  stok INT NOT NULL DEFAULT 1,
  image_path VARCHAR(255)
);

-- Tabel peminjaman
CREATE TABLE peminjaman (
  id SERIAL PRIMARY KEY,
  user_id INT NOT NULL,
  buku_id INT NOT NULL,
  tanggal_pinjam DATE NOT NULL DEFAULT CURRENT_DATE,
  tanggal_kembali DATE,
  status status_peminjaman_enum DEFAULT 'dipinjam',
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (buku_id) REFERENCES buku(id)
);

-- Tabel password_resets untuk fitur reset password
CREATE TABLE password_resets (
  id SERIAL PRIMARY KEY,
  email VARCHAR(100) NOT NULL,
  token VARCHAR(100) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  used BOOLEAN DEFAULT false
);

-- Tabel google_users untuk login dengan Google
CREATE TABLE google_users (
  id SERIAL PRIMARY KEY,
  google_id VARCHAR(100) NOT NULL UNIQUE,
  user_id INT NOT NULL,
  email VARCHAR(100) NOT NULL,
  FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Create demo admin user (password: admin123)
INSERT INTO users (username, password, email, role) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@perpustakaan.com', 'admin');

-- Create sample books
INSERT INTO buku (judul, pengarang, penerbit, tahun_terbit, genre, stok) VALUES
('Laskar Pelangi', 'Andrea Hirata', 'Bentang Pustaka', 2005, 'Novel', 10),
('Bumi Manusia', 'Pramoedya Ananta Toer', 'Hasta Mitra', 1980, 'Novel Sejarah', 5),
('Filosofi Teras', 'Henry Manampiring', 'Kompas', 2018, 'Filsafat', 8);
```

### 3. Configuration for Vercel

In your Vercel project settings, add the following environment variables:

| Variable | Value | Description |
|----------|-------|-------------|
| `VERCEL` | `1` | Indicates running on Vercel |
| `DB_HOST` | `db.YOURID.supabase.co` | Your Supabase host (from connection settings) |
| `DB_PORT` | `5432` or `6543` | Port for PostgreSQL (check Supabase connection info) |
| `DB_NAME` | `postgres` | Default database name |
| `DB_USER` | `postgres` | Default database user |
| `DB_PASS` | `your-password` | The database password you set |
| `VERCEL_BASE_URL` | `https://your-project.vercel.app/` | Your Vercel project URL |

### 4. Deploy Your Project

Run the deployment script:

```bash
./deploy.ps1
```

## Troubleshooting Database Issues

### Table Doesn't Exist Error

If you see errors like `relation "users" does not exist`, check:

1. That you've run all the SQL commands correctly
2. Your database connection parameters are correct
3. You're connected to the right database

### Authentication Failed

Check that your `DB_USER` and `DB_PASS` environment variables match your Supabase settings.

### Data Retrieval Issues

If you're connected but not seeing data:
1. Verify the SQL syntax in your application (PostgreSQL is more strict than MySQL)
2. Check for case sensitivity in table and column names
3. Review your SQL queries for PostgreSQL compatibility

## Maintaining Your Database

1. **Backups**: Regularly back up your database from Supabase dashboard
2. **Schema Changes**: Update both your local database and Supabase when changing schema
3. **Connection Pooling**: Enable connection pooling if your app receives higher traffic

## Additional Resources

- [Supabase Documentation](https://supabase.io/docs)
- [PostgreSQL Documentation](https://www.postgresql.org/docs/)
- [Vercel Environment Variables](https://vercel.com/docs/environment-variables)
