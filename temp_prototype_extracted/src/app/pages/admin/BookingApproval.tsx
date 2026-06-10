import { DashboardLayout } from '../../components/DashboardLayout';
import { Card } from '../../components/Card';
import { Button } from '../../components/Button';
import { StatusBadge } from '../../components/StatusBadge';
import { CheckCircle, XCircle, DollarSign, Info, Smartphone, AlertCircle } from 'lucide-react';
import { showToast } from '../../utils/toast';

export default function BookingApproval() {
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
      estimatedCost: 55000,
      notes: 'Full body kit from reputable manufacturer. Customer provided reference images.',
      submittedDate: 'March 28, 2026',
      reservationFee: {
        amount: 200,
        paymentMethod: 'gcash',
        referenceNumber: '1234567890123',
        verified: false,
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
      estimatedCost: 32000,
      notes: 'Custom stainless steel exhaust system with sport muffler.',
      submittedDate: 'March 29, 2026',
      reservationFee: {
        amount: 200,
        paymentMethod: 'maya',
        referenceNumber: '9876543210987',
        verified: false,
      },
    },
    {
      id: 3,
      customer: 'Ricardo Santos',
      contact: '+63 918 777 8888',
      service: 'Engine Customization',
      vehicle: 'Mitsubishi Lancer 2018',
      plateNumber: 'MNO 1122',
      preferredDate: 'April 12, 2026',
      preferredTime: '9:00 AM',
      status: 'pending' as const,
      estimatedCost: 95000,
      notes: 'Full engine rebuild with performance upgrades. Timing belt, pistons, and ECU tune.',
      submittedDate: 'March 30, 2026',
      reservationFee: {
        amount: 200,
        paymentMethod: 'gcash',
        referenceNumber: '5555666677778',
        verified: false,
      },
    },
  ];

  const handleVerifyPayment = (bookingId: number) => {
    showToast.success(`Reservation fee payment verified for Booking ${bookingId}!`);
  };

  const handleApprove = (bookingId: number, cost: number, paymentVerified: boolean) => {
    if (!paymentVerified) {
      showToast.error('Please verify the reservation fee payment first before approving the booking.');
      return;
    }
    showToast.success(`Booking ${bookingId} approved!\n\nEstimated Cost: ₱${cost.toLocaleString()}\n\nCustomer will be notified.`);
  };

  const handleReject = (bookingId: number) => {
    const reason = prompt('Please provide a reason for rejection:');
    if (reason) {
      showToast.error(`Booking ${bookingId} rejected.\n\nReason: ${reason}\n\nCustomer will be notified.`);
    }
  };

  const handleAdjustCost = (bookingId: number, currentCost: number) => {
    const newCost = prompt(`Adjust estimated cost for Booking ${bookingId}:`, currentCost.toString());
    if (newCost) {
      showToast.success(`Cost updated to ₱${Number(newCost).toLocaleString()}`);
    }
  };

  return (
    <DashboardLayout role="admin">
      <div className="space-y-6">
        {/* Header */}
        <div>
          <h1 className="text-3xl font-bold text-[#1F2937] mb-2">Booking Approval</h1>
          <p className="text-gray-600">Review and approve customer booking requests.</p>
        </div>

        {/* Stats */}
        <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
          <Card className="text-center">
            <p className="text-sm text-gray-600 mb-1">Pending Approval</p>
            <p className="text-3xl font-bold text-[#E63946]">{bookings.length}</p>
          </Card>
          <Card className="text-center">
            <p className="text-sm text-gray-600 mb-1">Approved Today</p>
            <p className="text-3xl font-bold text-green-600">8</p>
          </Card>
          <Card className="text-center">
            <p className="text-sm text-gray-600 mb-1">Rejected</p>
            <p className="text-3xl font-bold text-gray-600">2</p>
          </Card>
          <Card className="text-center">
            <p className="text-sm text-gray-600 mb-1">Total This Week</p>
            <p className="text-3xl font-bold text-[#457B9D]">15</p>
          </Card>
        </div>

        {/* Filters */}
        <Card>
          <div className="flex flex-wrap gap-2">
            <Button variant="primary" size="sm">Pending Approval</Button>
            <Button variant="ghost" size="sm">All Bookings</Button>
            <Button variant="ghost" size="sm">Approved</Button>
            <Button variant="ghost" size="sm">Rejected</Button>
          </div>
        </Card>

        {/* Bookings List */}
        <div className="space-y-6">
          {bookings.map((booking) => (
            <Card key={booking.id}>
              <div className="space-y-6">
                {/* Header */}
                <div className="flex flex-col lg:flex-row lg:items-start justify-between gap-4">
                  <div className="flex-1">
                    <div className="flex flex-wrap items-center gap-3 mb-3">
                      <h3 className="text-2xl font-bold text-[#1F2937]">{booking.service}</h3>
                      <StatusBadge status={booking.status}>Awaiting Approval</StatusBadge>
                    </div>
                    <p className="text-sm text-gray-600">
                      Submitted: {booking.submittedDate} • Booking ID: BK-{booking.id}
                    </p>
                  </div>
                </div>

                {/* Booking Details Grid */}
                <div className="grid grid-cols-1 md:grid-cols-3 gap-6 pb-6 border-b">
                  <div>
                    <h4 className="text-sm font-medium text-gray-600 mb-3">Customer Information</h4>
                    <div className="space-y-2">
                      <div>
                        <p className="text-xs text-gray-500">Name</p>
                        <p className="font-medium text-[#1F2937]">{booking.customer}</p>
                      </div>
                      <div>
                        <p className="text-xs text-gray-500">Contact</p>
                        <p className="text-sm text-gray-700">{booking.contact}</p>
                      </div>
                    </div>
                  </div>

                  <div>
                    <h4 className="text-sm font-medium text-gray-600 mb-3">Vehicle Details</h4>
                    <div className="space-y-2">
                      <div>
                        <p className="text-xs text-gray-500">Vehicle</p>
                        <p className="font-medium text-[#1F2937]">{booking.vehicle}</p>
                      </div>
                      <div>
                        <p className="text-xs text-gray-500">Plate Number</p>
                        <p className="text-sm text-gray-700">{booking.plateNumber}</p>
                      </div>
                    </div>
                  </div>

                  <div>
                    <h4 className="text-sm font-medium text-gray-600 mb-3">Preferred Schedule</h4>
                    <div className="space-y-2">
                      <div>
                        <p className="text-xs text-gray-500">Date</p>
                        <p className="font-medium text-[#1F2937]">{booking.preferredDate}</p>
                      </div>
                      <div>
                        <p className="text-xs text-gray-500">Time</p>
                        <p className="text-sm text-gray-700">{booking.preferredTime}</p>
                      </div>
                    </div>
                  </div>
                </div>

                {/* Notes & Cost */}
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                  <div>
                    <h4 className="text-sm font-medium text-gray-600 mb-3">Service Notes</h4>
                    <div className="bg-gray-50 rounded-lg p-4">
                      <p className="text-gray-700">{booking.notes}</p>
                    </div>
                  </div>

                  <div>
                    <h4 className="text-sm font-medium text-gray-600 mb-3">Cost Estimation</h4>
                    <div className="bg-gradient-to-r from-[#E63946] to-[#D62839] rounded-lg p-6 text-white">
                      <div className="flex items-start justify-between mb-2">
                        <div className="flex items-center gap-3">
                          <DollarSign size={32} />
                          <div>
                            <p className="text-sm text-white/80">Estimated Cost</p>
                            <p className="text-3xl font-bold">₱{booking.estimatedCost.toLocaleString()}</p>
                          </div>
                        </div>
                      </div>
                      <Button
                        variant="outline"
                        size="sm"
                        className="mt-4 bg-white text-[#E63946] border-white hover:bg-gray-100"
                        onClick={() => handleAdjustCost(booking.id, booking.estimatedCost)}
                      >
                        Adjust Cost
                      </Button>
                    </div>
                  </div>
                </div>

                {/* Reservation Fee Payment Verification */}
                <div className="bg-blue-50 border-2 border-[#457B9D] rounded-lg p-6">
                  <div className="flex items-center gap-2 mb-4">
                    <Smartphone className="text-[#457B9D]" size={24} />
                    <h4 className="text-lg font-bold text-[#1F2937]">Reservation Fee Payment Verification</h4>
                    {booking.reservationFee.verified ? (
                      <span className="ml-auto flex items-center gap-1 px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm font-medium">
                        <CheckCircle size={16} />
                        Verified
                      </span>
                    ) : (
                      <span className="ml-auto flex items-center gap-1 px-3 py-1 bg-amber-100 text-amber-700 rounded-full text-sm font-medium">
                        <AlertCircle size={16} />
                        Pending Verification
                      </span>
                    )}
                  </div>

                  <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div className="bg-white rounded-lg p-4">
                      <p className="text-xs text-gray-500 mb-1">Amount</p>
                      <p className="text-lg font-bold text-[#1F2937]">₱{booking.reservationFee.amount}</p>
                    </div>
                    <div className="bg-white rounded-lg p-4">
                      <p className="text-xs text-gray-500 mb-1">Payment Method</p>
                      <p className="text-lg font-bold text-[#1F2937] capitalize">{booking.reservationFee.paymentMethod}</p>
                    </div>
                    <div className="bg-white rounded-lg p-4">
                      <p className="text-xs text-gray-500 mb-1">Reference Number</p>
                      <p className="text-sm font-medium text-[#1F2937] break-all">{booking.reservationFee.referenceNumber}</p>
                    </div>
                  </div>

                  <div className="bg-amber-50 border-l-4 border-amber-500 p-4 mb-4">
                    <div className="flex gap-2">
                      <Info className="text-amber-600 flex-shrink-0" size={20} />
                      <div className="text-sm text-gray-700">
                        <p className="font-medium mb-1">Verification Instructions:</p>
                        <ol className="list-decimal list-inside space-y-1">
                          <li>Check your {booking.reservationFee.paymentMethod.toUpperCase()} account for incoming payment</li>
                          <li>Verify the reference number: <strong>{booking.reservationFee.referenceNumber}</strong></li>
                          <li>Confirm the amount is exactly ₱{booking.reservationFee.amount}</li>
                          <li>Click "Verify Payment" button once confirmed</li>
                        </ol>
                      </div>
                    </div>
                  </div>

                  {!booking.reservationFee.verified && (
                    <Button
                      variant="primary"
                      onClick={() => handleVerifyPayment(booking.id)}
                      className="w-full md:w-auto"
                    >
                      <CheckCircle size={20} className="mr-2" />
                      Verify Payment
                    </Button>
                  )}
                </div>

                {/* Action Buttons */}
                <div className="flex flex-wrap gap-3 pt-6 border-t">
                  <Button
                    variant="accent"
                    onClick={() => handleApprove(booking.id, booking.estimatedCost, booking.reservationFee.verified)}
                    disabled={!booking.reservationFee.verified}
                    className={!booking.reservationFee.verified ? 'opacity-50 cursor-not-allowed' : ''}
                  >
                    <CheckCircle size={20} className="mr-2" />
                    Approve Booking
                  </Button>
                  <Button
                    variant="outline"
                    onClick={() => handleReject(booking.id)}
                    className="text-red-600 hover:bg-red-50 hover:border-red-600"
                  >
                    <XCircle size={20} className="mr-2" />
                    Reject Booking
                  </Button>
                  <Button variant="secondary">
                    Contact Customer
                  </Button>
                </div>
              </div>
            </Card>
          ))}
        </div>
      </div>
    </DashboardLayout>
  );
}