# Vercel Deployment Guide for Perpustakaan Muflih

This guide explains how to properly deploy your Perpustakaan Muflih PHP application to Vercel using PostgreSQL (Supabase) as the database.

## Changes Made for Vercel Compatibility

### 1. Routing Configuration

We've updated the `vercel.json` configuration to properly route all requests through our enhanced router:

```json
{
  "version": 2,
  "functions": {
    "api/_router.php": {
      "runtime": "vercel-php@0.7.1"
    }
  },
  "routes": [
    { "src": "/assets/(.*)", "dest": "/assets/$1" },
    { "src": "/(.*)", "dest": "/api/_router.php" }
  ]
}
```

### 2. Enhanced Router

Created a smarter `api/_router.php` that:
- Properly handles query strings
- Routes requests to appropriate PHP files
- Includes necessary helper functions
- Sets the environment variables

### 3. Database Helpers

Created `api/helpers.php` with functions that:
- Detect whether running on Vercel or locally
- Execute queries appropriately for MySQL or PostgreSQL
- Provide unified fetch methods for results
- Handle errors consistently

### 4. Dashboard Optimization

Updated `dashboard.php` to:
- Work with both MySQL and PostgreSQL
- Use prepared statements for security
- Follow Vercel/Supabase best practices

## Deployment Process

1. **Set up Environment Variables**

   Run `setup_vercel_env.ps1` to configure all needed environment variables:
   
   ```powershell
   ./setup_vercel_env.ps1
   ```
   
   This will prompt you for:
   - Supabase database credentials
   - Google OAuth settings (if used)
   - Vercel deployment URL

2. **Configure Supabase Database**

   Follow the guide in `docs/SUPABASE_SETUP.md` to:
   - Create necessary PostgreSQL tables
   - Set up ENUM types
   - Add initial data

3. **Deploy to Vercel**

   Run the main deployment script:
   
   ```powershell
   ./deploy.ps1
   ```
   
   This script will:
   - Check environment variables
   - Ensure correct file structure
   - Deploy to Vercel with proper configuration

## Troubleshooting

### Only Index Page Works

If only your index.php works but other pages don't:

1. **Check the Router**: Ensure `api/_router.php` is correctly handling all paths
2. **File Paths**: Make sure all file includes use absolute paths with `__DIR__`
3. **Database Connection**: Verify PostgreSQL connection parameters
4. **Logs**: Check Vercel Function Logs for errors

### Database Connection Issues

If database operations fail:

1. **Environment Variables**: Verify DB_HOST, DB_USER, DB_PASS, etc. are set correctly
2. **SQL Syntax**: PostgreSQL has slightly different syntax than MySQL
3. **Database Structure**: Ensure tables exist and match the expected schema

### Login Problems

If authentication doesn't work:

1. **Session Handling**: Vercel Functions are stateless, ensure proper session management
2. **Password Hashing**: Confirm passwords are hashed consistently
3. **Error Logs**: Check for authentication errors in the Vercel logs

## Next Steps

After successful deployment:

1. Test all functionality thoroughly
2. Set up automatic database backups
3. Consider adding HTTPS redirection
4. Implement monitoring and error logging

## References

- [Vercel Serverless Functions](https://vercel.com/docs/functions)
- [PostgreSQL Documentation](https://www.postgresql.org/docs/)
- [Supabase Documentation](https://supabase.io/docs)
- [PHP on Vercel](https://github.com/vercel-community/php)
