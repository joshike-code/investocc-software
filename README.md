# 💼 Investocc Software

**Investocc** is a powerful investment platform built with a modular PHP backend and a modern ES6 JavaScript frontend. It offers an all-in-one solution for managing online investment operations, admin control, and secure user access.

This software was built by [Joshike-code](https://github.com/joshike-code) for **Degiant Software**.

---

## 🚀 Features

- ✅ PHP API backend (connect via `/backend/api/`)
- ✅ ES6 JavaScript frontend (bundled with Webpack, transpiled with Babel)
- ✅ JWT-based secure authentication
- ✅ MySQL database
- ✅ Modular admin system: Superadmin + multiple Admins with specific permissions
- ✅ Cron-powered investment updates
- ✅ SPA (Single Page Application) experience
- ✅ Service worker for better frontend caching
- ✅ Built-in error logging

---

## 🛠️ Installation Guide

> **Important:** Requires **PHP 8.2**

### Step-by-step Setup:

1. **Create a MySQL database**  
   Note down your:  
   - DB name  
   - DB user  
   - DB password  

2. **Visit your app’s domain in the browser**  
   Example: `https://yourdomain.com`  
   You should see the **Login Page**.

3. **Enter any login credentials**  
   This takes you to the **Installation Wizard**.

4. **Fill in your configuration details**  
   Including accurate DB credentials.

5. **Troubleshooting Tips:**
   - If you see **"Server Error"**, click **Try Again**.
   - If the error persists, check your **PHP version** (must be 8.2).
   - If the **Install button** is not clickable, try highlighting the support email field.

6. **Default Superadmin Credentials:**

Email: owner@investocc.com
Password: 1234

7. **Set your Degiant Passkey**  
You will be prompted to input the **passkey** after login.  
Obtain this from your merchant after purchasing the software.

8. **Configure Mail Settings**  
Go to **Admin Settings** and configure mail settings to enable OTP verification for new users.

9. **Set Up Cron**  
Add the script below to your server’s cron scheduler:
backend/cron/cron_update.php

10. **Done!**  
 You can now use your Investocc platform. Be sure to update it when prompted.

---

## ⚙️ Manual Installation (If Wizard Fails)

If the Installation Wizard doesn't work:

- Create a `.env` file inside `/backend`
- Use `.env.example` as a reference
- You can always update settings via this file

---

## 📂 Error Logs

- Server Errors → `backend/error/server_errors.log`
- Client Errors → `backend/error/client_errors.log`

These logs can help you diagnose any issues in the app.

---

## 🌐 API Overview

Your backend API base URL:
https://yourdomain.com/backend/api


Supports methods: `GET`, `POST`, `PUT`, `DELETE`

---

## 📬 Support

For assistance, open an [Issue](https://github.com/joshike-code/investocc-software/issues)  

---

## 🔒 Licensing

This software is commercial and licensed. Redistribution without permission is not allowed.

---