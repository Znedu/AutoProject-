import { useState } from 'react';
import { DashboardLayout } from '../../components/DashboardLayout';
import { Card } from '../../components/Card';
import { Button } from '../../components/Button';
import { StatusBadge } from '../../components/StatusBadge';
import { CheckCircle, XCircle, Calendar, DollarSign, AlertCircle, Eye } from 'lucide-react';
import { showToast } from '../../utils/toast';

export default function BookingQueue() {
  const [verifiedPayments, setVerifiedPayments] = useState<number[]>([]);

  const bookings = [
    {
      id: 1,
      customer: 'Carlos Reyes',
      contact: '+63 915 222 3333',
      service: 'Body Kit Installation',
      vehicle: 'Mazda RX-7 2019',
      plateNumber: 'DEF 9012',
      preferredDate: 'April 8, 2026',
      preferredTime: '10:00 AM',
      status: 'pending' as const,
      estimatedCost: '₱55,000',
      notes: 'Full body kit from reputable manufacturer',
      reservationFee: {
        amount: 200,
        paid: true,
        paymentMethod: 'GCash',
        referenceNumber: 'GCASH-20260407-1234567890',
        paymentDate: 'April 7, 2026',
        paymentTime: '3:45 PM',
      },
    },
    {
      id: 2,
      customer: 'Ana Garcia',
      contact: '+63 920 444 5555',
      service: 'Exhaust Fabrication',
      vehicle: 'Nissan Skyline 2020',
      plateNumber: 'JKL 7890',
      preferredDate: 'April 10, 2026',
      preferredTime: '2:00 PM',
      status: 'pending' as const,
      estimatedCost: '₱32,000',
      notes: 'Custom stainless steel exhaust system',
      reservationFee: {
        amount: 200,
        paid: true,
        paymentMethod: 'Maya',
        referenceNumber: 'MAYA-20260408-9876543210',
        paymentDate: 'April 8, 2026',
        paymentTime: '10:20 AM',
      },
    },
  ];

  const handleVerifyPayment = (bookingId: number) => {
    const confirmed = window.confirm(
      'Have you verified that the reference number matches the payment received in AutoProject-D GCash/Maya account?'
    );

    if (confirmed) {
      setVerifiedPayments([...verifiedPayments, bookingId]);
      showToast.success('Payment verified! You can now approve this booking.');
    }
  };

  const handleApprove = (bookingId: number) => {
    const isPaymentVerified = verifiedPayments.includes(bookingId);

    if (!isPaymentVerified) {
      showToast.error('Please verify the reservation fee payment before approving the booking.');
      return;
    }

    showToast.success(`Booking ${bookingId} approved! Customer will be notified.`);
  };

  const handleReject = (bookingId: number) => {
    const reason = prompt('Please provide a reason for rejection:');
    if (reason) {
      showToast.error(`Booking ${bookingId} rejected. Customer will be notified.`);
    }
  };

  const handleSchedule = (bookingId: number) => {
    showToast.info(`Opening schedule editor for booking ${bookingId}...`);
  };

  return (
    <DashboardLayout role="staff">
      <div className="space-y-6">
        {/* Header */}
        <div>
          <h1 className="text-3xl font-bold mb-2 text-gray-900 dark:text-white">Booking Queue</h1>
          <p className="text-gray-600 dark:text-gray-400">Review and manage customer booking requests.</p>
          <div className="mt-3 p-3 bg-blue-50 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-800 rounded-lg">
            <div className="flex items-start gap-2">
              <AlertCircle size={16} className="text-blue-600 dark:text-blue-400 mt-0.5 flex-shrink-0" />
              <p className="text-sm text-blue-800 dark:text-blue-200">
                <strong>Payment Verification Required:</strong> Before approving any booking, verify that the customer's reference number matches the payment received in AutoProject-D's GCash/Maya account.
              </p>
            </div>
          </div>
        </div>

        {/* Filters */}
        <Card>
          <div className="flex flex-wrap gap-2">
            <Button variant="primary" size="sm">All Bookings</Button>
            <Button variant="ghost" size="sm">Pending Review</Button>
            <Button variant="ghost" size="sm">Approved</Button>
            <Button variant="ghost" size="sm">Scheduled</Button>
            <Button variant="ghost" size="sm">Rejected</Button>
          </div>
        </Card>

        {/* Bookings List */}
        <div className="space-y-4">
          {bookings.map((booking) => (
            <Card key={booking.id}>
              <div className="space-y-4">
                {/* Header */}
                <div className="flex flex-col lg:flex-row lg:items-start justify-between gap-4">
                  <div>
                    <div className="flex flex-wrap items-center gap-3 mb-3">
                      <h3 className="text-xl font-bold text-gray-900 dark:text-white">{booking.service}</h3>
                      <StatusBadge status={booking.status}>Pending Review</StatusBadge>
                    </div>
                  </div>
                </div>

                {/* Booking Details */}
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                  <div>
                    <h4 className="text-sm font-medium mb-2 text-gray-900 dark:text-white">Customer Details</h4>
                    <p className="font-medium text-gray-900 dark:text-white">{booking.customer}</p>
                    <p className="text-sm text-gray-600 dark:text-gray-400">{booking.contact}</p>
                  </div>
                  <div>
                    <h4 className="text-sm font-medium mb-2 text-gray-900 dark:text-white">Vehicle Details</h4>
                    <p className="font-medium text-gray-900 dark:text-white">{booking.vehicle}</p>
                    <p className="text-sm text-gray-600 dark:text-gray-400">Plate: {booking.plateNumber}</p>
                  </div>
                  <div>
                    <h4 className="text-sm font-medium mb-2 text-gray-900 dark:text-white">Preferred Schedule</h4>
                    <p className="font-medium text-gray-900 dark:text-white">{booking.preferredDate}</p>
                    <p className="text-sm text-gray-600 dark:text-gray-400">{booking.preferredTime}</p>
                  </div>
                </div>

                {/* Payment Verification Section */}
                <div className={`p-4 rounded-lg border-2 ${
                  verifiedPayments.includes(booking.id)
                    ? 'bg-green-50 dark:bg-green-950/30 border-green-500'
                    : booking.reservationFee.paid
                    ? 'bg-amber-50 dark:bg-amber-950/30 border-amber-500'
                    : 'bg-red-50 dark:bg-red-950/30 border-red-500'
                }`}>
                  <div className="flex items-start gap-3">
                    <DollarSign size={24} className={`flex-shrink-0 ${
                      verifiedPayments.includes(booking.id)
                        ? 'text-green-600'
                        : booking.reservationFee.paid
                        ? 'text-amber-600'
                        : 'text-red-600'
                    }`} />
                    <div className="flex-1">
                      <div className="flex items-start justify-between gap-4 mb-3">
                        <div>
                          <h4 className="text-sm font-bold mb-1 text-gray-900 dark:text-white">
                            Reservation Fee (₱{booking.reservationFee.amount})
                          </h4>
                          {verifiedPayments.includes(booking.id) ? (
                            <div className="flex items-center gap-2">
                              <CheckCircle size={16} className="text-green-600" />
                              <span className="text-sm font-medium text-green-600">Payment Verified</span>
                            </div>
                          ) : booking.reservationFee.paid ? (
                            <div className="flex items-center gap-2">
                              <AlertCircle size={16} className="text-amber-600" />
                              <span className="text-sm font-medium text-amber-600">Pending Verification</span>
                            </div>
                          ) : (
                            <div className="flex items-center gap-2">
                              <XCircle size={16} className="text-red-600" />
                              <span className="text-sm font-medium text-red-600">Not Paid</span>
                            </div>
                          )}
                        </div>
                        {booking.reservationFee.paid && !verifiedPayments.includes(booking.id) && (
                          <Button
                            size="sm"
                            variant="outline"
                            onClick={() => handleVerifyPayment(booking.id)}
                            className="text-green-600 border-green-600 hover:bg-green-50 dark:hover:bg-green-950/30 dark:text-green-400 dark:border-green-400"
                          >
                            <Eye size={16} className="mr-2" />
                            Verify Payment
                          </Button>
                        )}
                      </div>

                      {booking.reservationFee.paid && (
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                          <div>
                            <p className="text-gray-600 dark:text-gray-400 mb-1">Payment Method</p>
                            <p className="font-medium text-gray-900 dark:text-white">{booking.reservationFee.paymentMethod}</p>
                          </div>
                          <div>
                            <p className="text-gray-600 dark:text-gray-400 mb-1">Payment Date & Time</p>
                            <p className="font-medium text-gray-900 dark:text-white">
                              {booking.reservationFee.paymentDate} • {booking.reservationFee.paymentTime}
                            </p>
                          </div>
                          <div className="md:col-span-2">
                            <p className="text-gray-600 dark:text-gray-400 mb-1">Reference Number</p>
                            <div className="flex items-center gap-2">
                              <code className="px-3 py-1.5 bg-gray-100 dark:bg-gray-800 rounded font-mono text-sm text-gray-900 dark:text-white border border-gray-300 dark:border-gray-600">
                                {booking.reservationFee.referenceNumber}
                              </code>
                              <Button
                                size="sm"
                                variant="ghost"
                                onClick={() => {
                                  navigator.clipboard.writeText(booking.reservationFee.referenceNumber);
                                  showToast.success('Reference number copied to clipboard');
                                }}
                                className="text-xs"
                              >
                                Copy
                              </Button>
                            </div>
                          </div>
                        </div>
                      )}

                      {!verifiedPayments.includes(booking.id) && booking.reservationFee.paid && (
                        <div className="mt-3 p-3 bg-white dark:bg-gray-900 rounded border border-amber-300 dark:border-amber-700">
                          <p className="text-xs text-amber-800 dark:text-amber-200 font-medium">
                            ⚠️ Important: Verify this reference number matches the payment received in AutoProject-D's {booking.reservationFee.paymentMethod} account before approving.
                          </p>
                        </div>
                      )}
                    </div>
                  </div>
                </div>

                {/* Cost & Notes */}
                <div className="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                  <div>
                    <h4 className="text-sm font-medium mb-2 text-gray-900 dark:text-white">Estimated Cost</h4>
                    <p className="text-2xl font-bold text-[#E63946]">{booking.estimatedCost}</p>
                  </div>
                  <div>
                    <h4 className="text-sm font-medium mb-2 text-gray-900 dark:text-white">Additional Notes</h4>
                    <p className="text-gray-700 dark:text-gray-300">{booking.notes}</p>
                  </div>
                </div>

                {/* Actions */}
                <div className="flex flex-wrap gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                  <Button
                    variant="outline"
                    size="sm"
                    onClick={() => handleApprove(booking.id)}
                    disabled={!verifiedPayments.includes(booking.id)}
                    className={`${
                      verifiedPayments.includes(booking.id)
                        ? 'text-blue-600 border-blue-600 hover:bg-blue-50 dark:hover:bg-blue-950/30 dark:text-blue-400 dark:border-blue-400'
                        : 'opacity-50 cursor-not-allowed'
                    }`}
                  >
                    <CheckCircle size={16} className="mr-2" />
                    Approve Booking
                    {!verifiedPayments.includes(booking.id) && (
                      <span className="ml-2 text-xs">(Verify Payment First)</span>
                    )}
                  </Button>
                  <Button
                    variant="secondary"
                    size="sm"
                    onClick={() => handleSchedule(booking.id)}
                  >
                    <Calendar size={16} className="mr-2" />
                    Schedule Service
                  </Button>
                  <Button
                    variant="outline"
                    size="sm"
                    onClick={() => handleReject(booking.id)}
                    className="text-red-600 border-red-600 hover:bg-red-50 dark:hover:bg-red-950/30 dark:text-red-400 dark:border-red-400"
                  >
                    <XCircle size={16} className="mr-2" />
                    Reject Booking
                  </Button>
                </div>
              </div>
            </Card>
          ))}
        </div>

        {/* Stats Summary */}
        <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
          <Card className="text-center">
            <p className="text-sm mb-1 text-gray-900 dark:text-white">Pending Review</p>
            <p className="text-2xl font-bold text-[#E63946]">5</p>
          </Card>
          <Card className="text-center">
            <p className="text-sm mb-1 text-gray-900 dark:text-white">Approved Today</p>
            <p className="text-2xl font-bold text-green-600">8</p>
          </Card>
          <Card className="text-center">
            <p className="text-sm mb-1 text-gray-900 dark:text-white">Scheduled</p>
            <p className="text-2xl font-bold text-[#457B9D]">12</p>
          </Card>
          <Card className="text-center">
            <p className="text-sm mb-1 text-gray-900 dark:text-white">Rejected</p>
            <p className="text-2xl font-bold text-gray-600 dark:text-gray-400">2</p>
          </Card>
        </div>
      </div>
    </DashboardLayout>
  );
}