# Grievance Redressal - Quick Reference

## 🚀 Access Points

### For Citizens
- **Login**: http://127.0.0.1:8000/citizen/login
- **My Grievances**: http://127.0.0.1:8000/citizen/grievances
- **Submit New**: http://127.0.0.1:8000/citizen/grievances/create

### For Admins
- **Manage Grievances**: http://127.0.0.1:8000/admin/grievances

## 📋 Feature Summary

### Citizen Portal Features
✅ Submit grievances with categories
✅ Upload photo evidence (max 5MB)
✅ Capture GPS location
✅ Track real-time status
✅ View admin responses
✅ See complete history
✅ Multi-language (EN/HI/MR)

### Admin Features (Already Built)
✅ View all citizen grievances
✅ Update status & priority
✅ Add remarks/responses
✅ Assign to officers
✅ Delete if needed

## 🎯 Grievance Categories

1. **Water Leakage** 💧
2. **Road Damage** 🛣️
3. **Electricity Issues** ⚡
4. **Sanitation** 🗑️
5. **Drainage Problems** 🌊
6. **Street Light** 💡
7. **Other** ⭕

## 📊 Status Workflow

```
Pending → In Progress → Resolved
              ↓
           Rejected
```

## 🎨 UI Components

### Sidebar Navigation
```
Grievance Redressal
├── My Grievances (List)
└── Submit Grievance (Form)
```

### Pages
1. **Index**: Card-based grievance list with filters
2. **Create**: Multi-step form with image upload
3. **Show**: Detailed view with timeline

## 🔐 Security

- CSRF protection
- Citizen authentication required
- File upload validation
- Input sanitization

## 📱 Mobile Support

✅ Responsive design
✅ Touch-friendly UI
✅ Mobile camera access
✅ GPS location capture

## 🌐 Languages

- English (EN)
- हिंदी (HI)
- मराठी (MR)

## ⚙️ Technical Stack

- **Backend**: Laravel PHP
- **Database**: MySQL
- **Frontend**: Blade Templates + CSS
- **Icons**: Font Awesome 6
- **Maps**: Google Maps (optional)
- **Location**: Browser Geolocation API

## 📝 Database

Table: `grievances`
- citizen_id (NEW - links to citizens table)
- ticket_no (auto-generated: GRV-YYYY-NNNN)
- category, description, image
- latitude, longitude, location_address
- status, priority
- admin_remarks, assigned_to
- resolved_at, timestamps

## 🎓 How to Use (Citizen)

1. **Login** to citizen portal
2. Click **"Submit Grievance"** in sidebar
3. **Select category** (click card)
4. **Write description** (min 20 chars)
5. **Upload photo** (optional)
6. **Get location** (optional, click GPS button)
7. **Submit** and get ticket number
8. **Track** in "My Grievances"

## 🎓 How to Manage (Admin)

1. Go to **Admin Panel** → **Grievances**
2. **View** all grievances (filter by status)
3. **Click** on any grievance
4. **Update status** (Pending → In Progress → Resolved)
5. **Add remarks** to communicate with citizen
6. **Set priority** if urgent
7. **Assign** to specific officer

## ✨ Key Benefits

### For Citizens
- Easy-to-use interface
- Photo evidence support
- Real-time tracking
- Transparent communication

### For Administration
- Organized complaint management
- Evidence-based resolution
- Accountability tracking
- Performance metrics

## 🔍 Testing URLs

Once logged in as a citizen, test these:
- `/citizen/grievances` - List page
- `/citizen/grievances/create` - Submit form
- `/citizen/grievances/1` - View details (if ID exists)

## 📞 Support

For issues or questions:
1. Check the full documentation: `GRIEVANCE_SYSTEM_IMPLEMENTATION.md`
2. Review the code in `app/Http/Controllers/Citizen/GrievanceController.php`
3. Check routes in `routes/web.php`

---

**Status**: ✅ Fully Implemented
**Date**: February 13, 2026
**Version**: 1.0
