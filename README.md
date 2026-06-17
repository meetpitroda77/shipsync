# ShipSync (Shipping Management System)

**ShipSync** is a modern, full-stack role-based logistics and shipping management platform. It is designed to handle the entire lifecycle of a shipment—from creation and payment to real-time tracking, reporting, and customer subscriptions.

The application uses a decoupled architecture with a **Laravel API** backend and a **React (Vite)** frontend.

## Technology Stack

- **Frontend**: React 19, Vite, React Router v7, Material UI (MUI), Tailwind CSS, Recharts/Chart.js (for analytics), Stripe React (for payments).
- **Backend**: Laravel 12, PHP 8.2+, Laravel Sanctum (API Auth), Laravel Cashier (Stripe Subscriptions), DOMPDF & Maatwebsite Excel (for exporting reports).
- **Real-time Capabilities**: Pusher and Laravel Echo for real-time web socket notifications.

## Core Features & Modules

### 1. Role-Based Access Control (RBAC)
The system routes users to completely different dashboards and access levels based on their roles:
- **Admin**: Has ultimate control. Can manage all users, system settings, global shipments, reports, and payments.
- **Customer**: Can create shipments, manage saved recipients, pay for shipments, manage Stripe subscriptions, and track their packages.
- **Agent / Staff**: Operational roles meant to manage specific shipments, update statuses, and view payment histories without full administrative privileges.

### 2. Shipment Management
- **Lifecycle Handling**: Users can create, update, delete, and track shipments.
- **Validation**: Built-in shipment validation before processing.
- **Invoicing**: Automatically generates downloadable PDF invoices for shipments.
- **Real-time Tracking**: A dedicated tracking page that allows users to see the exact status of a shipment.

### 3. Payments & Subscriptions (Stripe Integration)
- **One-off Payments**: Direct checkout for individual shipments.
- **SaaS Subscriptions**: Integrated with Laravel Cashier and Stripe Billing Portal. Customers can subscribe to different tiers (e.g., Upgrade to Pro), which dictate their limits (such as how many shipments they are permitted to create).
- **Webhook Handling**: Listens for Stripe webhooks to automatically update payment and subscription statuses in the database.

### 4. Recipient Address Book
Customers can maintain a list of saved recipients and their addresses to streamline the shipment creation process. 

### 5. Reporting & Analytics
- **Visual Dashboards**: Uses Recharts and Chart.js to display data visually on the dashboards.
- **Exportable Reports**: Admins can generate daily or general reports and export them to PDF, Excel, or CSV formats to track revenue, shipment volume, and operational metrics.

### 6. Real-time Notifications
Users receive live alerts through a notification bell in the UI (powered by Pusher/Echo) for important events like shipment status updates, payment confirmations, or subscription renewals.
