# DocuMind AI - Intelligent Document Management System

**University Capstone Project - Group 4**

> *An AI-powered web application that helps users upload, organize, analyze, and collaborate on documents using advanced artificial intelligence.*

---

## Table of Contents

- [Overview - What is DocuMind AI?](#overview---what-is-documind-ai)
- [Key Features](#key-features)
- [System Architecture](#system-architecture)
- [User Types & What They Can Do](#user-types--what-they-can-do)
- [AI Capabilities & Limitations](#ai-capabilities--limitations)
- [Storage & Upload Limits](#storage--upload-limits)
- [Security Features](#security-features)
- [How to Use DocuMind](#how-to-use-documind)
- [Technical Stack](#technical-stack)
- [Project Structure](#project-structure)
- [Setup Instructions](#setup-instructions)
- [Troubleshooting Guide](#troubleshooting-guide)
- [Group Member Contributions](#group-member-contributions)

---

## Overview - What is DocuMind AI?

### In Simple Terms

DocuMind AI is a **smart document storage and analysis platform**. Imagine you have:

- Long PDF reports
- Word documents  
- Research papers
- Business documents

Instead of reading them all yourself, DocuMind:

1. **Automatically reads your documents** using artificial intelligence
2. **Extracts key information** like summaries and keywords
3. **Lets you chat with your documents** - ask questions and get instant answers
4. **Organizes documents** into collections
5. **Lets you share documents** securely with other users
6. **Searches through all documents** instantly

### Real-World Example

You upload a 50-page quarterly business report. DocuMind:
- Generates a 3-paragraph executive summary automatically
- Extracts 8-10 key topics (revenue, expenses, targets, etc.)
- Lets you ask: *"What was our total revenue?"* and get the answer instantly
- Lets you share it with your team
- Lets your team ask their own questions about the same document

---

## Key Features

### Document Upload & Storage
- Upload **PDF and DOCX files** (up to 20MB each)
- Automatic text extraction from documents
- Secure file storage in the system
- Documents wait for **admin approval** before becoming public

### AI-Powered Analysis
- **Automatic Summaries**: AI reads your document and creates a 2-3 paragraph summary
- **Keyword Extraction**: AI identifies 5-10 most important topics from your document
- **Document Chat**: Ask questions and the AI searches the document to find answers
- **Multiple Response Styles**: Get answers that are:
  - **Concise** (short bullet points)
  - **Balanced** (clear and readable)
  - **Detailed** (comprehensive explanations)

### Collections & Organization
- Create **personal collections** to organize documents by topic
- Add documents to collections
- Color-coded collections for easy identification
- Keep private or share with specific people

### Document Sharing
- Share individual documents with other users
- Control permissions (view-only or edit)
- Track who has access to what
- Shared documents remain under original owner's control

### Advanced Search
- **Full-Text Search** across all documents
- Search by document name, keywords, or content
- Lightning-fast results using database indexing
- Filter by status (pending, approved)

### User Collaboration
- Invite other users to view documents
- See activity logs of who accessed what
- Get notifications about shared documents
- Comment on shared documents

### Admin Dashboard & Analytics
- View system-wide statistics
- Monitor user activity
- Approve/reject uploaded documents
- Manage user quotas and limits
- View top contributors

---

## System Architecture

### Technical Overview

DocuMind uses a **custom MVC (Model-View-Controller) framework** built entirely in PHP:

```
┌─────────────────────────────────────────────────────┐
│         User Interface (HTML/CSS/JavaScript)        │
└──────────────────┬──────────────────────────────────┘
                   │
┌──────────────────▼──────────────────────────────────┐
│  Router & Middleware (Authentication, CSRF)         │
└──────────────────┬──────────────────────────────────┘
                   │
┌──────────────────▼──────────────────────────────────┐
│    Controllers (Business Logic)                     │
│  - AuthController, DocumentController, etc.         │
└──────────────────┬──────────────────────────────────┘
                   │
┌──────────────────▼──────────────────────────────────┐
│    Models & Services (Data & Functionality)         │
│  - DocumentParser, AiService, etc.                  │
└──────────────────┬──────────────────────────────────┘
                   │
┌──────────────────▼──────────────────────────────────┐
│      Database (MySQL)                               │
└─────────────────────────────────────────────────────┘
```

### How a Document Gets Processed

```
User Uploads File
      ↓
Validation (File type, Size)
      ↓
Extract Text (PDF or DOCX)
      ↓
Save to Database
      ↓
Mark as "Pending" (Admin Review)
      ↓
Admin Reviews & Approves
      ↓
AI Generates Summary & Keywords (Auto)
      ↓
Document Ready for Use
```

---

## User Types & What They Can Do

### Regular User

A regular user can:

| Feature | What They Can Do |
|---------|-----------------|
| **Upload Documents** | Upload up to **10 documents** (or custom limit set by admin) |
| **File Size Limit** | Each file must be **under 20MB** |
| **AI Queries** | Ask AI up to **20 questions per day** about their documents |
| **View Own Documents** | See all documents they uploaded |
| **Share Documents** | Share their approved documents with other users |
| **Receive Shares** | View documents shared with them by other users |
| **Collections** | Create personal collections to organize their documents |
| **Search** | Search across all approved public documents |
| **Chat with Documents** | Ask questions to AI about their documents (within daily limit) |
| **View Activity** | See who accessed their documents and when |
| **CANNOT Do** | Can't approve/reject other users' documents, can't manage other users |

**Example User Journey:**
1. John registers as a regular user
2. Uploads his thesis (PDF file)
3. Document goes to admin for review
4. Admin approves it
5. John can now ask AI questions about his thesis
6. John shares it with his study group
7. Study group can view and ask questions (using their own AI quota)

### Admin User

An admin can do everything a regular user can PLUS:

| Feature | What They Can Do |
|---------|-----------------|
| **Approve Documents** | Review and approve documents from all users |
| **Reject Documents** | Reject documents that don't meet standards |
| **View All Documents** | See every document in the system (any status) |
| **Manage Users** | View, edit, and disable user accounts |
| **Adjust Quotas** | Set custom upload limits per user |
| **Set AI Limits** | Set custom daily AI query limits per user |
| **View Analytics** | See system statistics and user activity |
| **Review Logs** | Check activity logs for any user |
| **System Dashboard** | Monitor system health and usage |
| **CANNOT Do** | Cannot delete documents permanently (only admin can access archived), cannot modify document content |

**Example Admin Workflow:**
1. Admin logs in to Dashboard
2. Sees 5 documents pending approval
3. Reviews each document's content
4. Approves 4 documents
5. Rejects 1 (inappropriate content)
6. Adjusts upload limit for a power user from 10 to 50
7. Views analytics showing top 5 most active users

---

## AI Capabilities & Limitations

### What the AI Can Do

The system uses **Google Gemini AI** (Google's advanced language model):

#### Capabilities

1. **Document Summarization**
   - Reads up to 12,000 characters of your document
   - Generates a 2-3 paragraph summary
   - Extracts 5-10 key topics/keywords
   - Takes ~2-5 seconds per document

2. **Answer Questions About Documents**
   - Search through document text for answers
   - Answer specific questions like:
     - *"What is the main conclusion?"*
     - *"Who are the key stakeholders?"*
     - *"What are the budget numbers?"*
   - Provides direct quotes when relevant

3. **Multiple Response Styles**
   - **Concise Mode**: Short, bullet-point answers
   - **Balanced Mode**: Clear, readable answers (default)
   - **Detailed Mode**: In-depth, comprehensive responses

4. **Context Awareness**
   - Remembers previous questions in a conversation
   - Can follow up on previous answers
   - Understands relationships between concepts

### Limitations

1. **Document Size Limits**
   - Only processes first **12,000 characters** for summaries
   - For questions, uses **main document chunks** (first few pages)
   - Very large documents may miss content at the end

2. **Daily Query Limits**
   - Regular users: **20 AI questions per day** (resets daily)
   - Admin can adjust this limit per user
   - System tracks queries and prevents overuse

3. **File Type Limitations**
   - Only supports **PDF** and **DOCX** files
   - Image-heavy PDFs won't work well (no OCR)
   - Scanned PDFs with images may not extract text properly
   - Corrupted files may fail extraction

4. **AI Limitations**
   - AI can sometimes **hallucinate** (make up information)
   - Cannot process images within documents
   - May miss complex tables or charts
   - Language support is primarily **English** (other languages may work but not guaranteed)

5. **Accuracy**
   - AI is **not 100% accurate**
   - Always verify important information from original document
   - Best used for getting **quick overviews**, not precise details
   - Good for finding general information, not legal/medical specifics

### Daily AI Quota System

Each user has a daily AI limit:

```
Example: User has 20 daily AI queries
- Asks 5 questions about Document A
- Asks 8 questions about Document B  
- Has 7 questions remaining today
- Tomorrow: quota resets to 20 questions
```

**Note**: Admins can customize this limit per user (e.g., increase to 50 for power users or 5 for basic users)

---

## Storage & Upload Limits

### File Size Limits

| Limit Type | Maximum Size |
|-----------|-------------|
| **Per File** | 20 MB |
| **File Format** | PDF or DOCX only |
| **Total Per User** | Depends on admin setting |

### User Upload Quotas

| User Type | Default Limit | Can Be Changed? |
|-----------|--------------|-----------------|
| **Regular User** | 10 documents | Yes (by admin) |
| **Admin User** | 10 documents | Yes |
| **Power User (Custom)** | Custom | Yes (by admin) |

### How Upload Limits Work

```
Example: User has 10-document limit
- Upload 1st document: 9 remaining
- Upload 2nd document: 8 remaining
- Upload 3rd document: 7 remaining
- Delete 1st document: 10 available again
- Upload 4th document: 9 remaining
```

### Storage Location

All documents are stored in:
```
/storage/uploads/
```

Each file gets a unique UUID name to prevent conflicts:
```
Original: My_Report.pdf
Stored as: 550e8400-e29b-41d4-a716-446655440000.pdf
```

### Database Storage

- Document metadata stored in MySQL database
- Full text extracted and stored (searchable)
- Summaries and keywords stored as JSON
- Activity logs stored for audit trail

---

## Security Features

### 1. Password Security
- Passwords use **BCRYPT encryption** (military-grade)
- Never stored in plain text
- Each password has unique random "salt"
- Even admins cannot see user passwords

### 2. SQL Injection Prevention
- **100% of database queries** use prepared statements
- User input cannot manipulate database queries
- Prevents hackers from stealing data

### 3. CSRF Protection (Cross-Site Request Forgery)
- Every form has a unique security token
- Tokens regenerate for each request
- Only forms with valid token are processed
- Prevents unauthorized actions

### 4. XSS Protection (Cross-Site Scripting)
- All user input is sanitized before display
- HTML special characters are escaped
- Prevents malicious code injection
- Protects against script injection attacks

### 5. API Key Security
- Gemini API key stored in `.env` file (not in code)
- Never exposed to users or frontend
- Only accessible on backend server
- Changing API key doesn't require code changes

### 6. Session Management
- User sessions stored securely
- Session timeout after inactivity
- User must re-login on new browser
- Session data not accessible to other users

### 7. Authentication Required
- Most pages require login
- Public library accessible without login
- Protected routes redirect to login
- Invalid sessions automatically cleared

---

## How to Use DocuMind

### Getting Started

#### 1. **Create an Account**
   - Visit: `http://localhost/documind/public/login`
   - Click "Register"
   - Enter Name, Email, Password
   - Click "Create Account"

#### 2. **Login**
   - Enter Email and Password
   - Click "Sign In"
   - You'll see your Dashboard

### Regular User Guide

#### **Uploading a Document**

```
1. Click "Upload Document" button
2. Select a PDF or DOCX file (under 20MB)
3. Click "Upload"
4. Document appears as "Pending" (awaiting admin approval)
5. Wait for admin review
6. Once approved, you can use AI features
```

#### **Getting a Summary**

```
1. Go to "My Documents"
2. Click on an approved document
3. See "AI Summary" section
4. If empty, admin is still processing
5. Includes auto-generated keywords
```

#### **Chatting with a Document**

```
1. Open an approved document
2. Scroll to "Ask AI" section
3. Type a question: "What is the main topic?"
4. Click "Send"
5. AI searches document and responds
6. Keep asking follow-up questions
```

#### **Creating a Collection**

```
1. Click "Collections" in menu
2. Click "Create Collection"
3. Enter collection name
4. Choose color (optional)
5. Add documents to collection
```

#### **Sharing a Document**

```
1. Open your approved document
2. Click "Share" button
3. Enter recipient's email
4. Choose permission (View Only)
5. Click "Share"
6. Recipient gets notification
```

### Admin Guide

#### **Approving Documents**

```
1. Click "Admin Dashboard"
2. See "Pending Documents" count
3. Click "Review Pending" 
4. View document content
5. Click "Approve" or "Reject"
6. Document becomes available (if approved)
```

#### **Managing Users**

```
1. Click "Users" in admin menu
2. See list of all users
3. Click on user to view details
4. Can view their documents and activity
5. Can adjust their upload/AI limits
```

#### **Viewing Analytics**

```
1. Click "Dashboard"
2. See system statistics
3. View most active users
4. See recent activity log
5. Monitor system health
```

---

## Technical Stack

### Backend Technologies

| Technology | Purpose |
|-----------|---------|
| **PHP 8.1+** | Server-side language |
| **MySQL 5.7+** | Database storage |
| **PDO** | Database connections |
| **Composer** | Package management |
| **Google Gemini API** | AI capabilities |

### Frontend Technologies

| Technology | Purpose |
|-----------|---------|
| **HTML5** | Structure |
| **CSS3** | Styling |
| **JavaScript** | Interactivity |
| **Bootstrap/Tailwind** | UI Framework |

### Key PHP Libraries

```
- vlucas/phpdotenv - Environment variables
- phpoffice/phpword - DOCX parsing
- smalot/pdfparser - PDF parsing
- guzzlehttp/guzzle - HTTP requests
- league/oauth2-google - Google OAuth
```

---

## Project Structure

The following shows the complete directory structure of the DocuMind AI project:

```
documind/
├── app/
│   ├── Controllers/
│   │   ├── AdminController.php        (Admin dashboard and management)
│   │   ├── AiController.php           (AI chat and query handling)
│   │   ├── AnalyticsController.php    (System analytics and reporting)
│   │   ├── AuthController.php         (Login, registration, authentication)
│   │   ├── BaseController.php         (Parent controller with common methods)
│   │   ├── CollectionController.php   (Document collections management)
│   │   ├── DashboardController.php    (User dashboard)
│   │   ├── DocumentController.php     (Document upload, view, delete)
│   │   ├── PublicController.php       (Public document library)
│   │   ├── ShareController.php        (Document sharing)
│   │   └── UserController.php         (User profile management)
│   │
│   ├── Models/
│   │   ├── BaseModel.php              (Base model with common DB methods)
│   │   ├── ActivityLog.php            (Activity tracking)
│   │   ├── AiChat.php                 (Chat history storage)
│   │   ├── AiUsage.php                (AI usage statistics)
│   │   ├── Collection.php             (Document collections)
│   │   ├── Document.php               (Document records)
│   │   ├── DocumentShare.php          (Shared document records)
│   │   ├── Notification.php           (User notifications)
│   │   └── User.php                   (User account records)
│   │
│   ├── Services/
│   │   ├── AiService.php              (Google Gemini API integration)
│   │   ├── AnalyticsService.php       (Analytics calculations)
│   │   ├── DocumentParser.php         (PDF/DOCX text extraction)
│   │   ├── Logger.php                 (Activity logging)
│   │   └── ShareService.php           (Document sharing logic)
│   │
│   ├── Middleware/
│   │   ├── AdminMiddleware.php        (Admin-only route protection)
│   │   ├── AuthMiddleware.php         (Authentication verification)
│   │   └── CsrfMiddleware.php         (CSRF token validation)
│   │
│   └── Helpers/
│       ├── Csrf.php                   (CSRF token generation)
│       ├── Database.php               (Database singleton)
│       ├── FileHelper.php             (File operations)
│       └── Validator.php              (Input validation)
│
├── config/
│   ├── app.php                        (Application settings)
│   ├── database.php                   (Database configuration)
│   └── oauth.php                      (OAuth settings)
│
├── database/
│   ├── schema.sql                     (Database structure)
│   └── migration_phase5.sql           (Phase 5 updates)
│
├── public/
│   ├── index.php                      (Application entry point)
│   └── assets/
│       ├── css/                       (Stylesheets)
│       │   ├── app.css
│       │   ├── dashboard.css
│       │   ├── admin.css
│       │   └── responsive.css
│       ├── js/                        (JavaScript files)
│       │   ├── app.js
│       │   ├── ai-chat.js
│       │   ├── document-upload.js
│       │   └── analytics.js
│       └── images/                    (Static images)
│
├── storage/
│   ├── uploads/                       (Uploaded documents)
│   ├── logs/                          (Application logs)
│   └── sessions/                      (Session files)
│
├── views/
│   ├── layouts/
│   │   ├── app.php                    (Main authenticated layout)
│   │   ├── auth.php                   (Authentication layout)
│   │   └── public.php                 (Public layout)
│   │
│   ├── admin/
│   │   ├── dashboard.php              (Admin dashboard)
│   │   ├── users.php                  (User management list)
│   │   ├── user-detail.php            (User details page)
│   │   ├── documents.php              (Document management)
│   │   ├── pending-documents.php      (Document approval)
│   │   └── activity-log.php           (Activity logs)
│   │
│   ├── ai/
│   │   └── chat.php                   (AI chat interface)
│   │
│   ├── auth/
│   │   ├── login.php                  (Login form)
│   │   └── register.php               (Registration form)
│   │
│   ├── collections/
│   │   ├── index.php                  (Collections list)
│   │   └── view.php                   (Collection details)
│   │
│   ├── dashboard/
│   │   └── index.php                  (User dashboard)
│   │
│   ├── documents/
│   │   ├── index.php                  (Documents list)
│   │   ├── view.php                   (Document viewer)
│   │   ├── upload.php                 (Upload form)
│   │   └── search.php                 (Search results)
│   │
│   ├── errors/
│   │   ├── 403.php                    (Forbidden error page)
│   │   ├── 404.php                    (Not found error page)
│   │   ├── 429.php                    (Rate limit error page)
│   │   └── 500.php                    (Server error page)
│   │
│   ├── public/
│   │   ├── document.php               (Public document view)
│   │   └── library.php                (Public document library)
│   │
│   └── user/
│       ├── profile.php                (User profile)
│       ├── settings.php               (User settings)
│       └── activity.php               (User activity)
│
├── vendor/                            (Composer dependencies)
│   ├── autoload.php
│   ├── composer/
│   ├── guzzlehttp/                    (HTTP client)
│   ├── league/                        (OAuth2 libraries)
│   ├── phpoffice/                     (DOCX parsing)
│   ├── psr/                           (PSR standards)
│   ├── smalot/                        (PDF parsing)
│   ├── symfony/                       (Utilities)
│   ├── vlucas/                        (Environment loader)
│   └── ...
│
├── .env.example                       (Environment template)
├── .gitignore                         (Git ignore rules)
├── composer.json                      (PHP dependencies)
├── composer.lock                      (Dependency lock file)
├── index.php                          (Root entry point)
├── README.md                          (This file)
├── router.php                         (URL routing configuration)
└── LICENSE                            (Project license)
```

### Key Directory Descriptions

**app/Controllers/** - Handles incoming HTTP requests and coordinates with models and services

**app/Models/** - Database table interactions using PDO prepared statements

**app/Services/** - Business logic including AI integration, document parsing, and analytics

**app/Middleware/** - Request interceptors for security and authentication

**app/Helpers/** - Utility functions for common operations

**config/** - Application configuration and environment-specific settings

**database/** - Database schema and migration scripts

**public/** - Web-accessible directory with entry point and static assets

**storage/** - Non-web-accessible directory for uploads and logs

**views/** - HTML template files organized by feature

**vendor/** - Third-party PHP packages installed via Composer

---

## Setup Instructions

### Prerequisites

- PHP 8.1 or higher
- MySQL 5.7 or higher
- Composer (PHP package manager)
- XAMPP/MAMP (or similar local server)
- Google API Key for Gemini

### Step-by-Step Installation

#### **1. Clone/Download the Project**

```bash
# Place the project in your htdocs folder
# Windows: C:\xampp\htdocs\documind
# Mac: /Applications/MAMP/htdocs/documind
```

#### **2. Install PHP Dependencies**

```bash
# Navigate to project directory
cd documind

# Install Composer packages
composer install
```

#### **3. Set Up Environment Variables**

```bash
# Copy example .env file
cp .env.example .env

# Edit .env file with your settings:
# - APP_NAME=DocuMind
# - APP_ENV=local
# - APP_URL=http://localhost/documind
# - DB_HOST=localhost
# - DB_USER=root
# - DB_PASS=(leave empty for XAMPP default)
# - DB_NAME=documind
# - GEMINI_API_KEY=your_key_here
```

#### **4. Create Database**

```bash
# Open phpMyAdmin: http://localhost/phpmyadmin
# Create new database named: documind
# Import database schema:
# - Click "Import" tab
# - Select file: database/schema.sql
# - Click "Go"
```

#### **5. Set Folder Permissions**

```bash
# Make storage folder writable
chmod -R 755 storage/
chmod -R 755 public/assets/
```

#### **6. Start Server**

```bash
# Start XAMPP/MAMP
# Navigate to: http://localhost/documind/public/library
```

### Getting a Gemini API Key

1. Visit: https://makersuite.google.com/app/apikey
2. Click "Create API Key"
3. Copy the key
4. Paste into `.env` file as `GEMINI_API_KEY`

---

## Troubleshooting Guide

### Common Issues

#### **"Database Connection Error"**
- **Solution**: Check `.env` file has correct database credentials
- Verify MySQL is running
- Make sure database `documind` exists
- Check username and password

#### **"Composer packages not found"**
- **Solution**: Run `composer install` in project directory
- Delete `vendor/` folder and `composer.lock`
- Run `composer install` again

#### **"API Key Error" or "AI features not working"**
- **Solution**: Check `.env` file has valid Gemini API key
- Verify API key is active on Google Cloud Console
- Check internet connection is working
- Verify API quota hasn't been exceeded

#### **"Cannot upload files"**
- **Solution**: Check `storage/uploads/` folder exists and is writable
- File must be under 20MB
- File must be PDF or DOCX only
- Check `upload_max_mb` setting in config/app.php

#### **"White screen / 500 error"**
- **Solution**: Check error logs in `storage/logs/`
- Ensure PHP version is 8.1+
- Verify all Composer packages installed
- Check `.env` file is properly configured

#### **"Session/Login issues"**
- **Solution**: Clear browser cookies and cache
- Delete session files in `storage/sessions/`
- Try incognito/private browser window
- Restart your server

#### **"PDF/DOCX not extracting text"**
- **Solution**: Ensure file is not corrupted
- Try re-exporting document with different tool
- Some scanned PDFs may not work (need OCR)
- File size might be hitting limits

#### **"Admin can't approve documents"**
- **Solution**: Check user role is set to 'admin' in database
- Verify admin middleware is working
- Check `AuthMiddleware` in app/Middleware/
- Try logging out and back in

---

## Support & Documentation

### For Group Members

- **Ask in group chat** before making major changes
- **Document your changes** in comments
- **Test locally** before pushing code
- **Keep database schema updated** if making DB changes

### Important Files to Review

1. **app/Controllers/DocumentController.php** - Document upload/viewing
2. **app/Services/AiService.php** - AI integration
3. **app/Services/DocumentParser.php** - Text extraction logic
4. **database/schema.sql** - Database structure
5. **config/app.php** - System configuration
6. **router.php** - URL routing rules

### Useful Links

- Google Gemini API: https://ai.google.dev/
- PHP Documentation: https://www.php.net/docs.php
- MySQL Documentation: https://dev.mysql.com/doc/
- Bootstrap Documentation: https://getbootstrap.com/docs/

---

## Group Member Contributions

| Name | Role | Contributions |
|------|------|---------------|
| Member 1 | DevOps & QA | Database optimization, Testing & debugging, Document parser improvements |
| Member 2 | Full-Stack Developer | Backend API development, Database design, User authentication system |
| Member 3 | AI Integration Specialist | Google Gemini API integration, Prompt engineering, AI response optimization |
| Member 4 | Frontend Developer | UI/UX design, Document viewer interface, Dashboard components |
| Member 5 | DevOps & QA | Database optimization, Testing & debugging, Document parser improvements |

**Responsibilities Breakdown:**
- Sarah: Designed the MVC architecture and implemented core controllers and middleware
- Michael: Integrated AI services and handled document analysis features
- Priya: Built responsive frontend and implemented admin dashboard UI
- David: Optimized database queries, implemented FULLTEXT search, and handled error handling

---

## Additional Notes

### Performance Tips

- Clear browser cache if pages load slowly
- Use incognito mode if session issues occur
- Large documents (10MB+) take longer to process
- AI responses take 2-5 seconds (API latency)

### Current Limitations

- No OCR for scanned PDFs (images not processed)
- No real-time collaboration (can't edit together)
- No document versioning (can't see history)
- No mobile app (web-only)

### Future Improvements

- WebSocket support for real-time chat
- Advanced OCR for image PDFs
- Document version history
- Mobile responsive design improvements
- Dark mode UI
- Multi-language AI responses
- Document tagging system
- Advanced analytics export

### License & Disclaimer

This project is developed as a university capstone assignment. The Google Gemini API usage is subject to Google's terms and quotas.

---

**Last Updated:** April 2026
**Version:** 1.0
**Status:** Complete & Functional
