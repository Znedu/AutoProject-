import { useState } from 'react';
import { useNavigate } from 'react-router';
import { DashboardLayout } from '../../components/DashboardLayout';
import { Card } from '../../components/Card';
import { Button } from '../../components/Button';
import { CreditCard, Upload, AlertCircle, CheckCircle } from 'lucide-react';
import { showToast } from '../../utils/toast';

export default function Payment() {
  const navigate = useNavigate();
  const [selectedPayment, setSelectedPayment] = useState<'gcash' | 'maya'>('gcash');
  const [uploadedFile, setUploadedFile] = useState<string | null>(null);

  const bookingDetails = {
    id: '12345',
    service: 'Engine Customization',
    vehicle: 'Honda Civic 2020',
    reservationFee: '₱5,000',
    totalEstimate: '₱75,000',
  };

  const handleFileUpload = (e: React.ChangeEvent<HTMLInputElement>) => {
    if (e.target.files && e.target.files[0]) {
      setUploadedFile(e.target.files[0].name);
    }
  };

  const handleConfirmPayment = () => {
    if (!uploadedFile) {
      showToast.error('Please upload payment screenshot');
      return;
    }
    showToast.success('Payment submitted successfully! Your booking is now confirmed.');
    navigate('/customer/track');
  };

  return (
    <DashboardLayout role="customer">
      <div className="max-w-4xl mx-auto space-y-6">
        {/* Header */}
        <div>
          <h1 className="text-3xl font-bold text-[#1F2937] mb-2">Payment</h1>
          <p className="text-gray-600">Complete your reservation payment to confirm booking.</p>
        </div>

        {/* Booking Summary */}
        <Card>
          <h2 className="text-xl font-bold text-[#1F2937] mb-4">Booking Summary</h2>
          <div className="space-y-3">
            <div className="flex justify-between">
              <span className="text-gray-600">Booking ID</span>
              <span className="font-medium text-[#1F2937]">BK-{bookingDetails.id}</span>
            </div>
            <div className="flex justify-between">
              <span className="text-gray-600">Service</span>
              <span className="font-medium text-[#1F2937]">{bookingDetails.service}</span>
            </div>
            <div className="flex justify-between">
              <span className="text-gray-600">Vehicle</span>
              <span className="font-medium text-[#1F2937]">{bookingDetails.vehicle}</span>
            </div>
            <div className="flex justify-between border-t pt-3">
              <span className="text-gray-600">Total Estimate</span>
              <span className="font-medium text-[#1F2937]">{bookingDetails.totalEstimate}</span>
            </div>
            <div className="flex justify-between items-center bg-[#E63946]/10 p-4 rounded-lg">
              <span className="font-bold text-[#1F2937]">Reservation Fee (Required)</span>
              <span className="text-2xl font-bold text-[#E63946]">{bookingDetails.reservationFee}</span>
            </div>
          </div>
        </Card>

        {/* Payment Method Selection */}
        <Card>
          <h2 className="text-xl font-bold text-[#1F2937] mb-4">Payment Method</h2>
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <button
              onClick={() => setSelectedPayment('gcash')}
              className={`p-6 border-2 rounded-lg transition-all ${
                selectedPayment === 'gcash'
                  ? 'border-[#E63946] bg-[#E63946]/5'
                  : 'border-gray-200 hover:border-[#E63946]'
              }`}
            >
              <div className="text-center">
                <div className="text-4xl font-bold text-blue-600 mb-2">GCash</div>
                <p className="text-sm text-gray-600">Pay via GCash</p>
              </div>
            </button>
            <button
              onClick={() => setSelectedPayment('maya')}
              className={`p-6 border-2 rounded-lg transition-all ${
                selectedPayment === 'maya'
                  ? 'border-[#E63946] bg-[#E63946]/5'
                  : 'border-gray-200 hover:border-[#E63946]'
              }`}
            >
              <div className="text-center">
                <div className="text-4xl font-bold text-green-600 mb-2">Maya</div>
                <p className="text-sm text-gray-600">Pay via Maya</p>
              </div>
            </button>
          </div>
        </Card>

        {/* Payment Instructions */}
        <Card className="bg-[#457B9D]/10">
          <h2 className="text-xl font-bold text-[#1F2937] mb-4">Payment Instructions</h2>
          <ol className="space-y-3 list-decimal list-inside text-gray-700">
            <li>Send ₱5,000 reservation fee to our {selectedPayment === 'gcash' ? 'GCash' : 'Maya'} number: <strong>0912 345 6789</strong></li>
            <li>Take a screenshot of your payment confirmation</li>
            <li>Upload the screenshot below</li>
            <li>Click "Confirm Payment" to complete the process</li>
          </ol>
        </Card>

        {/* Upload Payment Screenshot */}
        <Card>
          <h2 className="text-xl font-bold text-[#1F2937] mb-4">Upload Payment Screenshot</h2>
          <div className="space-y-4">
            <label className="flex flex-col items-center justify-center w-full h-48 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer hover:border-[#E63946] transition-colors bg-gray-50">
              <div className="flex flex-col items-center justify-center pt-5 pb-6">
                <Upload size={48} className="text-gray-400 mb-3" />
                <p className="mb-2 text-sm text-gray-600">
                  <span className="font-semibold">Click to upload</span> or drag and drop
                </p>
                <p className="text-xs text-gray-500">PNG, JPG or JPEG (MAX. 5MB)</p>
                {uploadedFile && (
                  <div className="mt-4 flex items-center gap-2 text-green-600">
                    <CheckCircle size={20} />
                    <span className="font-medium">{uploadedFile}</span>
                  </div>
                )}
              </div>
              <input
                type="file"
                className="hidden"
                accept="image/*"
                onChange={handleFileUpload}
              />
            </label>
          </div>
        </Card>

        {/* Actions */}
        <div className="flex gap-4">
          <Button
            variant="outline"
            onClick={() => navigate('/customer/bookings')}
          >
            Cancel
          </Button>
          <Button
            variant="accent"
            size="lg"
            onClick={handleConfirmPayment}
            className="flex-1"
          >
            Confirm Payment
          </Button>
        </div>
      </div>
    </DashboardLayout>
  );
}