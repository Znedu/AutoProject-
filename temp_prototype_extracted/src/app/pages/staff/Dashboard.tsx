import { DashboardLayout } from '../../components/DashboardLayout';
import { StatCard } from '../../components/Card';
import { ClipboardList, MessageSquare, Clock, CheckCircle } from 'lucide-react';
import { Link, useNavigate } from 'react-router';
import { Button } from '../../components/Button';
import { Card } from '../../components/Card';
import { StatusBadge } from '../../components/StatusBadge';

export default function StaffDashboard() {
  const navigate = useNavigate();
  const pendingBookings = [
    {
      id: 1,
      customer: 'Carlos Reyes',
      service: 'Body Kit Installation',
      date: 'April 8, 2026',
      status: 'pending' as const,
    },
  ];

  const openTickets = [
    {
      id: 1,
      customer: 'Juan Dela Cruz',
      subject: 'Question about paint warranty',
      status: 'open' as const,
      date: 'March 30, 2026',
    },
    {
      id: 2,
      customer: 'Maria Santos',
      subject: 'Reschedule booking request',
      status: 'in-progress' as const,
      date: 'March 28, 2026',
    },
  ];

  const handleReviewBooking = (bookingId: number) => {
    // Navigate to booking queue with specific booking selected
    navigate('/staff/booking-queue', { state: { selectedBookingId: bookingId } });
  };

  const handleRespondToTicket = (ticketId: number) => {
    // Navigate to customer assistance with specific ticket selected
    navigate('/staff/assistance', { state: { selectedTicketId: ticketId } });
  };

  return (
    <DashboardLayout role="staff">
      <div className="space-y-8">
        {/* Header */}
        <div>
          <h1 className="text-3xl font-bold text-gray-900 dark:text-white mb-2">Staff Dashboard</h1>
          <p className="text-gray-600 dark:text-gray-400">Manage bookings and assist customers.</p>
        </div>

        {/* Stats */}
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          <StatCard
            title="Pending Bookings"
            value="5"
            icon={<Clock size={24} />}
            color="red"
          />
          <StatCard
            title="Scheduled Today"
            value="3"
            icon={<ClipboardList size={24} />}
            color="blue"
          />
          <StatCard
            title="Open Tickets"
            value="8"
            icon={<MessageSquare size={24} />}
            color="charcoal"
          />
          <StatCard
            title="Resolved Today"
            value="12"
            icon={<CheckCircle size={24} />}
            color="green"
          />
        </div>

        {/* Quick Actions */}
        <div className="bg-white dark:bg-[#151515]/60 border border-gray-200 dark:border-gray-800 rounded-lg shadow-md p-6">
          <h2 className="text-xl font-bold mb-4 text-gray-900 dark:text-white">Quick Actions</h2>
          <div className="flex flex-wrap gap-4">
            <Link to="/staff/booking-queue">
              <Button variant="accent" className="bg-red-600 hover:bg-red-700 text-white border-red-600 hover:border-red-700">
                View Booking Queue
              </Button>
            </Link>
            <Link to="/staff/assistance">
              <Button variant="secondary" className="bg-blue-600 hover:bg-blue-700 text-white border-blue-600 hover:border-blue-700">
                Customer Assistance
              </Button>
            </Link>
          </div>
        </div>

        {/* Pending Bookings */}
        <Card>
          <div className="flex justify-between items-center mb-4">
            <h2 className="text-xl font-bold text-gray-900 dark:text-white">Pending Bookings Approval</h2>
            <Link to="/staff/booking-queue">
              <Button variant="ghost" size="sm">View All</Button>
            </Link>
          </div>
          {pendingBookings.length > 0 ? (
            <div className="space-y-3">
              {pendingBookings.map((booking) => (
                <div key={booking.id} className="border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-[#0B0B0B]/40 rounded-lg p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                  <div>
                    <h3 className="font-bold mb-1 text-gray-900 dark:text-white">{booking.service}</h3>
                    <p className="text-sm text-gray-600 dark:text-gray-400">Customer: {booking.customer}</p>
                    <p className="text-sm text-gray-600 dark:text-gray-400">Date: {booking.date}</p>
                  </div>
                  <div className="flex items-center gap-3">
                    <StatusBadge status={booking.status}>Pending</StatusBadge>
                    <Button
                      size="sm"
                      variant="secondary"
                      onClick={() => handleReviewBooking(booking.id)}
                    >
                      Review
                    </Button>
                  </div>
                </div>
              ))}
            </div>
          ) : (
            <p className="text-gray-600 dark:text-gray-400">No pending bookings</p>
          )}
        </Card>

        {/* Open Support Tickets */}
        <Card>
          <div className="flex justify-between items-center mb-4">
            <h2 className="text-xl font-bold text-gray-900 dark:text-white">Open Support Tickets</h2>
            <Link to="/staff/assistance">
              <Button variant="ghost" size="sm">View All</Button>
            </Link>
          </div>
          {openTickets.length > 0 ? (
            <div className="space-y-3">
              {openTickets.map((ticket) => (
                <div key={ticket.id} className="border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-[#0B0B0B]/40 rounded-lg p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                  <div>
                    <h3 className="font-bold mb-1 text-gray-900 dark:text-white">{ticket.subject}</h3>
                    <p className="text-sm text-gray-600 dark:text-gray-400">Customer: {ticket.customer}</p>
                    <p className="text-sm text-gray-600 dark:text-gray-400">Date: {ticket.date}</p>
                  </div>
                  <div className="flex items-center gap-3">
                    <StatusBadge status={ticket.status}>
                      {ticket.status === 'open' ? 'Open' : 'In Progress'}
                    </StatusBadge>
                    <Button
                      size="sm"
                      variant="secondary"
                      onClick={() => handleRespondToTicket(ticket.id)}
                    >
                      Respond
                    </Button>
                  </div>
                </div>
              ))}
            </div>
          ) : (
            <p className="text-gray-600 dark:text-gray-400">No open tickets</p>
          )}
        </Card>

        {/* Today's Schedule */}
        <Card>
          <h2 className="text-xl font-bold mb-4 text-gray-900 dark:text-white">Today's Service Schedule</h2>
          <div className="space-y-3">
            <div className="flex items-center gap-4 pb-3 border-b border-gray-200 dark:border-gray-700">
              <div className="text-center">
                <p className="text-sm text-gray-600 dark:text-gray-400">Time</p>
                <p className="font-bold text-gray-900 dark:text-white">10:00 AM</p>
              </div>
              <div className="flex-1">
                <p className="font-medium text-gray-900 dark:text-white">Engine Customization</p>
                <p className="text-sm text-gray-600 dark:text-gray-400">Maria Santos - Honda Civic 2020</p>
              </div>
              <StatusBadge status="confirmed">Confirmed</StatusBadge>
            </div>
            <div className="flex items-center gap-4 pb-3 border-b border-gray-200 dark:border-gray-700">
              <div className="text-center">
                <p className="text-sm text-gray-600 dark:text-gray-400">Time</p>
                <p className="font-bold text-gray-900 dark:text-white">2:00 PM</p>
              </div>
              <div className="flex-1">
                <p className="font-medium text-gray-900 dark:text-white">Exhaust Fabrication</p>
                <p className="text-sm text-gray-600 dark:text-gray-400">Pedro Lopez - Mazda RX-7 2019</p>
              </div>
              <StatusBadge status="confirmed">Confirmed</StatusBadge>
            </div>
          </div>
        </Card>
      </div>
    </DashboardLayout>
  );
}