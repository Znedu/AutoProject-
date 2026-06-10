import { createBrowserRouter } from "react-router";
import Landing from "./pages/Landing";
import Login from "./pages/Login";
import Register from "./pages/Register";
import CustomerDashboard from "./pages/customer/Dashboard";
import BookService from "./pages/customer/BookService";
import MyBookings from "./pages/customer/MyBookings";
import TrackService from "./pages/customer/TrackService";
import SupportTickets from "./pages/customer/SupportTickets";
import Profile from "./pages/customer/Profile";
import Payment from "./pages/customer/Payment";
import MechanicDashboard from "./pages/mechanic/Dashboard";
import AssignedJobs from "./pages/mechanic/AssignedJobs";
import ServiceNotes from "./pages/mechanic/ServiceNotes";
import StaffDashboard from "./pages/staff/Dashboard";
import BookingQueue from "./pages/staff/BookingQueue";
import CustomerAssistance from "./pages/staff/CustomerAssistance";
import AdminDashboard from "./pages/admin/Dashboard";
import UserManagement from "./pages/admin/UserManagement";
import BookingApproval from "./pages/admin/BookingApproval";
import ServiceManagement from "./pages/admin/ServiceManagement";
import Reports from "./pages/admin/Reports";

export const router = createBrowserRouter([
  {
    path: "/",
    Component: Landing,
  },
  {
    path: "/login",
    Component: Login,
  },
  {
    path: "/register",
    Component: Register,
  },
  // Customer Routes
  {
    path: "/customer",
    Component: CustomerDashboard,
  },
  {
    path: "/customer/book-service",
    Component: BookService,
  },
  {
    path: "/customer/bookings",
    Component: MyBookings,
  },
  {
    path: "/customer/track",
    Component: TrackService,
  },
  {
    path: "/customer/support",
    Component: SupportTickets,
  },
  {
    path: "/customer/profile",
    Component: Profile,
  },
  {
    path: "/customer/payment/:bookingId",
    Component: Payment,
  },
  // Mechanic Routes
  {
    path: "/mechanic",
    Component: MechanicDashboard,
  },
  {
    path: "/mechanic/jobs",
    Component: AssignedJobs,
  },
  {
    path: "/mechanic/notes",
    Component: ServiceNotes,
  },
  // Staff Routes
  {
    path: "/staff",
    Component: StaffDashboard,
  },
  {
    path: "/staff/booking-queue",
    Component: BookingQueue,
  },
  {
    path: "/staff/assistance",
    Component: CustomerAssistance,
  },
  // Admin Routes
  {
    path: "/admin",
    Component: AdminDashboard,
  },
  {
    path: "/admin/users",
    Component: UserManagement,
  },
  {
    path: "/admin/approvals",
    Component: BookingApproval,
  },
  {
    path: "/admin/services",
    Component: ServiceManagement,
  },
  {
    path: "/admin/reports",
    Component: Reports,
  },
]);
