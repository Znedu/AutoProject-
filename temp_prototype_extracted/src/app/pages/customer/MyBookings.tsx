import { useState, useMemo } from 'react';
import { DashboardLayout } from '../../components/DashboardLayout';
import { Card } from '../../components/Card';
import { StatusBadge } from '../../components/StatusBadge';
import { Button } from '../../components/Button';
import { Link, useNavigate } from 'react-router';
import { CheckCircle2, XCircle, AlertCircle, MessageCircle } from 'lucide-react';
import { toast } from 'sonner';

export default function MyBookings() {
  const navigate = useNavigate();
  const [selectedFilter, setSelectedFilter] = useState<'all' | 'pending' | 'confirmed' | 'in-progress' | 'completed'>('all');
  const [canceledBookings, setCanceledBookings] = useState<number[]>([]);

  const bookings = [
    {
      id: 1,
      service: 'Engine Customization',
      vehicle: 'Honda Civic 2020',
      plateNumber: 'ABC 1234',
      date: 'April 5, 2026',
      time: '10:00 AM',
      status: 'confirmed' as const,
      estimatedCost: '₱75,000',
      reservationFeePaid: true,
      reservationFeeVerified: true,
    },
    {
      id: 2,
      service: 'Paint Job',
      vehicle: 'Toyota Supra 2021',
      plateNumber: 'XYZ 5678',
      date: 'March 28, 2026',
      time: '2:00 PM',
      status: 'in-progress' as const,
      estimatedCost: '₱45,000',
      reservationFeePaid: true,
      reservationFeeVerified: true,
    },
    {
      id: 3,
      service: 'Body Kit Installation',
      vehicle: 'Mazda RX-7 2019',
      plateNumber: 'DEF 9012',
      date: 'March 25, 2026',
      time: '9:00 AM',
      status: 'pending' as const,
      estimatedCost: '₱55,000',
      reservationFeePaid: true,
      reservationFeeVerified: false,
    },
    {
      id: 4,
      service: 'Turbo Installation',
      vehicle: 'Subaru WRX 2022',
      plateNumber: 'GHI 3456',
      date: 'March 20, 2026',
      time: '11:00 AM',
      status: 'completed' as const,
      estimatedCost: '₱120,000',
      reservationFeePaid: true,
      reservationFeeVerified: true,
    },
    {
      id: 5,
      service: 'Exhaust Fabrication',
      vehicle: 'Nissan Skyline 2020',
      plateNumber: 'JKL 7890',
      date: 'March 15, 2026',
      time: '3:00 PM',
      status: 'rejected' as const,
      estimatedCost: '₱28,000',
      reservationFeePaid: false,
      reservationFeeVerified: false,
    },
  ];

  const getStatusDisplay = (status: typeof bookings[0]['status']) => {
    const displays = {
      'pending': 'Pending Verification',
      'approved': 'Approved',
      'rejected': 'Rejected',
      'waiting-payment': 'Waiting Payment',
      'confirmed': 'Confirmed',
      'in-progress': 'In Progress',
      'completed': 'Completed',
    };
    return displays[status];
  };

  // Handle cancel booking
  const handleCancelBooking = (bookingId: number) => {
    if (window.confirm('Are you sure you want to cancel this booking?')) {
      setCanceledBookings([...canceledBookings, bookingId]);
      toast.success('Booking canceled successfully');
    }
  };

  // Handle contact support
  const handleContactSupport = (bookingId: number, serviceName: string) => {
    navigate('/customer/support', {
      state: {
        bookingId,
        serviceName,
        prefilledSubject: `Issue with Booking #${bookingId} - ${serviceName}`
      }
    });
  };

  // Filter bookings based on selected filter
  const filteredBookings = useMemo(() => {
    // Filter out canceled bookings
    const activeBookings = bookings.filter(b => !canceledBookings.includes(b.id));

    if (selectedFilter === 'all') return activeBookings;
    return activeBookings.filter((booking) => {
      if (selectedFilter === 'in-progress') return booking.status === 'in-progress';
      return booking.status === selectedFilter;
    });
  }, [selectedFilter, canceledBookings]);

  return (
    <DashboardLayout role="customer">
      <div className="space-y-6">
        {/* Header Section */}
        <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
          <div>
            <h1 className="text-3xl font-bold mb-2 text-gray-900 dark:text-white">My Bookings</h1>
            <p className="text-gray-600 dark:text-gray-400">View and manage all your service bookings.</p>
          </div>
          <Link to="/customer/book-service">
            <Button variant="accent">New Booking</Button>
          </Link>
        </div>

        {/* Filters */}
        <Card>
          <div className="flex flex-wrap gap-2">
            <Button
              variant={selectedFilter === 'all' ? 'primary' : 'ghost'}
              size="sm"
              onClick={() => setSelectedFilter('all')}
            >
              All
            </Button>
            <Button
              variant={selectedFilter === 'pending' ? 'primary' : 'ghost'}
              size="sm"
              onClick={() => setSelectedFilter('pending')}
            >
              Pending
            </Button>
            <Button
              variant={selectedFilter === 'confirmed' ? 'primary' : 'ghost'}
              size="sm"
              onClick={() => setSelectedFilter('confirmed')}
            >
              Confirmed
            </Button>
            <Button
              variant={selectedFilter === 'in-progress' ? 'primary' : 'ghost'}
              size="sm"
              onClick={() => setSelectedFilter('in-progress')}
            >
              In Progress
            </Button>
            <Button
              variant={selectedFilter === 'completed' ? 'primary' : 'ghost'}
              size="sm"
              onClick={() => setSelectedFilter('completed')}
            >
              Completed
            </Button>
          </div>
        </Card>

        {/* Bookings List */}
        <div className="space-y-4">
          {filteredBookings.length === 0 ? (
            <Card>
              <div className="text-center py-8">
                <p className="text-gray-600 dark:text-gray-400">No bookings found for this filter.</p>
              </div>
            </Card>
          ) : (
            filteredBookings.map((booking) => (
              <Card key={booking.id} hover>
                <div className="flex flex-col lg:flex-row gap-6">
                  <div className="flex-1 space-y-3">
                    <div className="flex items-start justify-between gap-4">
                      <div>
                        <h3 className="text-xl font-bold mb-1 text-gray-900 dark:text-white">
                          {booking.service}
                        </h3>
                        <p className="text-gray-700 dark:text-gray-300">
                          {booking.vehicle} • {booking.plateNumber}
                        </p>
                      </div>
                      <StatusBadge status={booking.status}>
                        {getStatusDisplay(booking.status)}
                      </StatusBadge>
                    </div>

                    <div className="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
                      <div>
                        <p className="text-gray-600 dark:text-gray-400">Date</p>
                        <p className="font-medium text-gray-900 dark:text-white">{booking.date}</p>
                      </div>
                      <div>
                        <p className="text-gray-600 dark:text-gray-400">Time</p>
                        <p className="font-medium text-gray-900 dark:text-white">{booking.time}</p>
                      </div>
                      <div>
                        <p className="text-gray-600 dark:text-gray-400">Estimated Cost</p>
                        <p className="font-medium text-[#E63946]">{booking.estimatedCost}</p>
                      </div>
                    </div>

                    {/* Reservation Fee Status */}
                    <div className="pt-3 border-t border-gray-200 dark:border-gray-700">
                      <div className="flex items-center gap-2 text-sm">
                        <span className="text-gray-700 dark:text-gray-300">Reservation Fee (₱200):</span>
                        {booking.reservationFeePaid ? (
                          booking.reservationFeeVerified ? (
                            <span className="flex items-center gap-1 text-green-600 font-medium">
                              <CheckCircle2 size={16} />
                              Paid & Verified
                            </span>
                          ) : (
                            <span className="flex items-center gap-1 text-amber-600 font-medium">
                              <AlertCircle size={16} />
                              Pending Verification
                            </span>
                          )
                        ) : (
                          <span className="flex items-center gap-1 text-red-600 font-medium">
                            <XCircle size={16} />
                            Not Paid
                          </span>
                        )}
                      </div>
                    </div>
                  </div>

                  <div className="flex lg:flex-col gap-2 justify-end">
                    <Link to="/customer/track">
                      <Button variant="secondary" size="sm" className="whitespace-nowrap">
                        Track Status
                      </Button>
                    </Link>

                    <Button
                      variant="secondary"
                      size="sm"
                      className="whitespace-nowrap"
                      onClick={() => handleContactSupport(booking.id, booking.service)}
                    >
                      <MessageCircle size={16} className="mr-1" />
                      Contact Support
                    </Button>

                    {booking.status === 'approved' && (
                      <Link to={`/customer/payment/${booking.id}`}>
                        <Button variant="accent" size="sm" className="whitespace-nowrap bg-green-600 hover:bg-green-700 text-white">
                          Pay Now
                        </Button>
                      </Link>
                    )}

                    {booking.status === 'pending' && (
                      <Button
                        variant="outline"
                        size="sm"
                        className="whitespace-nowrap bg-red-600 hover:bg-red-700 text-white border-red-600"
                        onClick={() => handleCancelBooking(booking.id)}
                      >
                        Cancel
                      </Button>
                    )}
                  </div>
                </div>
              </Card>
            ))
          )}
        </div>
      </div>
    </DashboardLayout>
  );
}