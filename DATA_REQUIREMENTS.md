# Water Tax Data Import - Required Fields

**Project:** Gram Panchayat Portal  
**Document Version:** 1.0  
**Date:** 22-01-2026

---

## 📊 Current Data Fields (Already Available)

Based on the existing Excel sheet (`water_demand_english_all_sheets`), the following fields are currently available:

| # | Column Name | Description |
|---|-------------|-------------|
| 1 | A.No. | Serial number |
| 2 | Customer no | Customer identifier | Grahak kramank|
| 3 | Customer Name | Name of the taxpayer |
| 4 | The monthly amount of water bill is Rs. | Monthly bill amount |
| 5 | From Sun Mahe to Sun Mahe | Billing period |
| 6 | the rest | Outstanding/arrears |
| 7 | Oversize 10% Rs. | Penalty amount (10%) |
| 8 | Bill no. | Bill number |
| 9 | Receipt no. | Receipt number |
| 10 | Date | Payment date |
| 11 | Amount paid Rs. | Amount paid |
| 12 | Shera | Remarks |

---

## ✅ Mandatory Additional Fields Required

### 1. Contact & Identity Information

| Field Name | Required | Purpose | Example |
|------------|----------|---------|---------|
| **Mobile Number** | ✅ Mandatory | SMS/WhatsApp notifications, OTP login for citizen portal | 9876543210 |
| **Aadhaar Number** | Optional | Unique identity verification | XXXX-XXXX-1234 |
| **Email Address** | Optional | Digital receipts, email notifications | example@gmail.com |

---

### 2. Address & Location Information

| Field Name | Required | Purpose | Example |
|------------|----------|---------|---------|
| **Ward Number** | ✅ Mandatory | Ward-wise reports, filtering, admin assignment | 5 |
| **Full Address** | ✅ Mandatory | Property identification & correspondence | House No. 123, Main Road |
| **Property/House Number** | ✅ Mandatory | Unique property reference | H-123 |
| **Locality Name** | ✅ Mandatory | Easy search & area-wise grouping | Neral East |
| **Pincode** | Optional | Postal correspondence | 410101 |

---

### 3. Water Connection Details (Optional)

| Field Name | Required | Purpose | Example |
|------------|----------|---------|---------|
| **Connection Type** | ✅ Mandatory | Different rates for different types | Domestic / Commercial / Industrial |
| **Connection Size** | ✅ Mandatory | Rate calculation based on pipe size | 1/2" / 3/4" / 1" |
| **Connection Date** | Optional | Track when connection was issued | 01-01-2020 |
| **Meter Number** | If Metered | For metered water connections | M-12345 |
| **Connection Status** | ✅ Mandatory | Active/Inactive tracking | Active / Disconnected / Temporary |
| **Number of Taps** | Optional | For billing calculation | 2 |

---

### 4. Property & Ownership Information (Optional)

| Field Name | Required | Purpose | Example |
|------------|----------|---------|---------|
| **Owner Type** | Optional | Ownership classification | Owner / Tenant |
| **Property Type** | Optional | Rate classification | Residential / Commercial / Mixed |

---

### 5. Billing & Financial Data

| Field Name | Required | Purpose | Example |
|------------|----------|---------|---------|
| **Opening Balance (Arrears)** | ✅ Mandatory | Import existing outstanding dues | ₹500 |
| **Monthly Rate** | ✅ Mandatory | Fixed monthly water charge | ₹100 |
| **Rate Category** | Optional | Special rates for certain categories | BPL / APL / General / Exempted |
| **Last Payment Date** | Optional | Track payment history | 15-12-2025 |
| **Billing Cycle** | Optional | Frequency of billing | Monthly / Quarterly / Annual |

---

## 📝 Recommended Excel Template Format

Please provide data in the following format:

| Column # | Column Name | Required | Data Type | Example Value |
|----------|-------------|----------|-----------|---------------|
| 1 | Customer No | ✅ | Text | WTR001 |
| 2 | Customer Name | ✅ | Text | Ram Kumar Sharma |
| 3 | Mobile Number | ✅ | Number (10 digits) | 9876543210 |
| 4 | **Ward Number** | ✅ | Number | 5 |
| 5 | **House/Property Number** | ✅ | Text | H-123 |
| 6 | **Address** | ✅ | Text | Near Temple, Main Road |
| 7 | **Locality/Mohalla** | ✅ | Text | Neral East |
| 9 | **Connection Type** | ✅ | Text | Domestic / Commercial |
| 10 | **Connection Size** | ✅ | Text | 1/2" / 3/4" / 1" |
| 11 | Connection Date | ❌ | Date | 01-01-2020 |
| 12 | Meter Number | ❌ | Text | M-12345 |
| 13 | **Opening Balance** | ✅ | Number | 500 |
| 14 | **Monthly Rate** | ✅ | Number | 100 |
| 15 | **Connection Status** | ✅ | Text | Active / Disconnected |
| 16 | Property Type | ❌ | Text | Residential / Commercial |
| 17 | Aadhaar Number | ❌ | Text (masked) | XXXX-XXXX-1234 |
| 18 | Email | ❌ | Text | example@gmail.com |

---

## ⚠️ Important Notes for Data Collection

### Data Quality Guidelines

1. **Unique Customer ID**
   - Ensure each customer has a unique identifier
   - No duplicate customer numbers

2. **Mobile Number Format**
   - Must be 10 digits
   - Only Indian mobile numbers (starting with 6-9)
   - This is critical for OTP-based citizen login

3. **Ward Number**
   - Must match the ward structure of the Gram Panchayat
   - Used for generating ward-wise reports
   - Required for assigning ward-level admins

4. **Opening Balance**
   - Include all outstanding dues as of the import date
   - This ensures historical arrears are not lost

5. **Connection Type & Size**
   - Different rates may apply based on connection type
   - Commercial connections typically have higher rates

6. **Data Cleaning Before Import**
   - Remove duplicate entries
   - Standardize spellings of localities
   - Verify all mobile numbers are valid
   - Ensure consistent date formats (DD-MM-YYYY)

---

## 📱 Citizen Portal Features Requiring This Data

| Feature | Required Fields |
|---------|-----------------|
| Citizen Login (OTP) | Mobile Number |
| View Outstanding Bills | Customer No, Opening Balance |
| Online Payment | All billing fields |
| Payment Notifications | Mobile Number, Email |
| Ward-wise Reports | Ward Number |
| Connection Details | Connection Type, Size, Status |
| Address Verification | Full Address, Locality |

---

## 📞 Contact for Clarifications

For any questions regarding data format or requirements, please contact the development team.

---

*Document prepared for client meeting on 22-01-2026*
