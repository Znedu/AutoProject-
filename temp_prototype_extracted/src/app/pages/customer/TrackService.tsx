import { useState } from 'react';
import { DashboardLayout } from '../../components/DashboardLayout';
import { Card } from '../../components/Card';
import { CheckCircle, Clock, Camera, X, ZoomIn } from 'lucide-react';

export default function TrackService() {
  const [selectedPhoto, setSelectedPhoto] = useState<string | null>(null);

  const trackingData = {
    service: 'Paint Job',
    vehicle: 'Toyota Supra 2021',
    bookingId: 'BK-2026-0328',
    currentStage: 2,
    stages: [
      {
        name: 'Booking Confirmed',
        date: 'March 28, 2026 - 2:00 PM',
        completed: true,
      },
      {
        name: 'Vehicle Received',
        date: 'March 29, 2026 - 9:00 AM',
        completed: true,
      },
      {
        name: 'Service Ongoing',
        date: 'In Progress',
        completed: false,
      },
      {
        name: 'Quality Inspection',
        date: 'Pending',
        completed: false,
      },
      {
        name: 'Completed',
        date: 'Pending',
        completed: false,
      },
    ],
    notes: [
      {
        date: 'March 30, 2026',
        time: '2:30 PM',
        message: 'Surface preparation completed. Starting primer application.',
        author: 'Mechanic: John Santos',
        photos: [
          {
            url: 'https://images.unsplash.com/photo-1619642751034-765dfdf7c58e?w=800',
            caption: 'Surface sanding completed',
          },
          {
            url: 'https://images.unsplash.com/photo-1632823469942-6c3f5b6c3c9e?w=800',
            caption: 'Primer application in progress',
          },
        ],
      },
      {
        date: 'March 29, 2026',
        time: '11:15 AM',
        message: 'Vehicle inspection completed. Beginning disassembly and masking.',
        author: 'Mechanic: John Santos',
        photos: [
          {
            url: 'https://images.unsplash.com/photo-1625047509168-a7026f36de04?w=800',
            caption: 'Vehicle inspection - front view',
          },
          {
            url: 'https://images.unsplash.com/photo-1627454820516-913e9e6c1d62?w=800',
            caption: 'Masking process started',
          },
          {
            url: 'https://images.unsplash.com/photo-1621939514649-280e2ee25f60?w=800',
            caption: 'Parts disassembly',
          },
        ],
      },
    ],
  };

  return (
    <DashboardLayout role="customer">
      <div className="max-w-4xl mx-auto space-y-6">
        {/* Header */}
        <div>
          <h1 className="text-3xl font-bold mb-2 text-gray-900 dark:text-white">Track Service</h1>
          <p className="text-gray-600 dark:text-gray-400">Real-time tracking of your vehicle service.</p>
          <div className="mt-3 p-3 bg-green-50 dark:bg-green-950/30 border border-green-200 dark:border-green-800 rounded-lg">
            <div className="flex items-start gap-2">
              <Camera size={16} className="text-green-600 dark:text-green-400 mt-0.5 flex-shrink-0" />
              <p className="text-sm text-green-800 dark:text-green-200">
                <strong>Photo Updates Enabled:</strong> Our mechanics will send you photos showing the progress of your vehicle's service. Click on any photo to view it in full size.
              </p>
            </div>
          </div>
        </div>

        {/* Service Info */}
        <Card>
          <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <p className="text-sm mb-1 text-gray-600 dark:text-gray-400">Service Type</p>
              <p className="font-bold text-gray-900 dark:text-white">{trackingData.service}</p>
            </div>
            <div>
              <p className="text-sm mb-1 text-gray-600 dark:text-gray-400">Vehicle</p>
              <p className="font-bold text-gray-900 dark:text-white">{trackingData.vehicle}</p>
            </div>
            <div>
              <p className="text-sm mb-1 text-gray-600 dark:text-gray-400">Booking ID</p>
              <p className="font-bold text-gray-900 dark:text-white">{trackingData.bookingId}</p>
            </div>
          </div>
        </Card>

        {/* Progress Bar */}
        <Card>
          <h2 className="text-xl font-bold mb-6 text-gray-900 dark:text-white">Service Progress</h2>
          <div className="relative">
            {/* Progress Line */}
            <div className="absolute left-6 top-0 bottom-0 w-1 bg-gray-300 dark:bg-gray-700">
              <div
                className="bg-[#E63946] w-full transition-all duration-500"
                style={{ height: `${(trackingData.currentStage / (trackingData.stages.length - 1)) * 100}%` }}
              />
            </div>

            {/* Stages */}
            <div className="space-y-8 relative">
              {trackingData.stages.map((stage, index) => (
                <div key={index} className="flex items-start gap-4 relative">
                  <div
                    className={`flex-shrink-0 w-12 h-12 rounded-full flex items-center justify-center z-10 ${
                      stage.completed
                        ? 'bg-[#E63946] text-white'
                        : index === trackingData.currentStage
                        ? 'bg-[#457B9D] text-white'
                        : 'bg-gray-200 dark:bg-gray-700 text-gray-400 dark:text-gray-500'
                    }`}
                  >
                    {stage.completed ? (
                      <CheckCircle size={24} />
                    ) : index === trackingData.currentStage ? (
                      <Clock size={24} />
                    ) : (
                      <span className="text-lg font-bold">{index + 1}</span>
                    )}
                  </div>
                  <div className="flex-1 pt-2">
                    <h3 className={`font-bold mb-1 ${stage.completed ? 'text-gray-900 dark:text-white' : 'text-gray-500 dark:text-gray-400'}`}>
                      {stage.name}
                    </h3>
                    <p className="text-sm text-gray-600 dark:text-gray-400">{stage.date}</p>
                  </div>
                </div>
              ))}
            </div>
          </div>
        </Card>

        {/* Service Notes */}
        <Card>
          <div className="flex items-center gap-2 mb-4">
            <h2 className="text-xl font-bold text-gray-900 dark:text-white">Service Updates</h2>
            <Camera size={20} className="text-[#E63946]" />
          </div>
          <div className="space-y-6">
            {trackingData.notes.map((note, index) => (
              <div key={index} className="border-l-4 border-[#E63946] pl-4 py-3 bg-gray-50 dark:bg-[#0B0B0B]/40 rounded">
                <div className="flex items-start justify-between gap-4 mb-2">
                  <div>
                    <p className="text-sm font-medium text-gray-600 dark:text-gray-400">
                      {note.date} • {note.time}
                    </p>
                    <p className="text-sm font-medium text-gray-700 dark:text-gray-300 mt-1">{note.author}</p>
                  </div>
                  {note.photos && note.photos.length > 0 && (
                    <div className="flex items-center gap-1 text-sm text-[#E63946] bg-red-50 dark:bg-red-950/30 px-2 py-1 rounded">
                      <Camera size={14} />
                      <span>{note.photos.length} photo{note.photos.length > 1 ? 's' : ''}</span>
                    </div>
                  )}
                </div>
                <p className="mb-3 text-gray-900 dark:text-gray-200">{note.message}</p>

                {/* Photo Gallery */}
                {note.photos && note.photos.length > 0 && (
                  <div className="grid grid-cols-2 sm:grid-cols-3 gap-2 mt-3">
                    {note.photos.map((photo, photoIndex) => (
                      <div
                        key={photoIndex}
                        className="relative group cursor-pointer"
                        onClick={() => setSelectedPhoto(photo.url)}
                      >
                        <div className="aspect-square bg-gray-200 dark:bg-gray-700 rounded-lg overflow-hidden">
                          <img
                            src={photo.url}
                            alt={photo.caption}
                            className="w-full h-full object-cover transition-transform group-hover:scale-110"
                          />
                        </div>
                        <div className="absolute inset-0 bg-black/0 group-hover:bg-black/40 transition-colors rounded-lg flex items-center justify-center">
                          <ZoomIn
                            size={24}
                            className="text-white opacity-0 group-hover:opacity-100 transition-opacity"
                          />
                        </div>
                        <p className="text-xs text-gray-600 dark:text-gray-400 mt-1 line-clamp-1">
                          {photo.caption}
                        </p>
                      </div>
                    ))}
                  </div>
                )}
              </div>
            ))}
          </div>
        </Card>

        {/* Photo Lightbox */}
        {selectedPhoto && (
          <div
            className="fixed inset-0 bg-black/90 flex items-center justify-center z-50 p-4"
            onClick={() => setSelectedPhoto(null)}
          >
            <button
              onClick={() => setSelectedPhoto(null)}
              className="absolute top-4 right-4 text-white hover:text-gray-300"
            >
              <X size={32} />
            </button>
            <img
              src={selectedPhoto}
              alt="Full size"
              className="max-w-full max-h-full object-contain"
              onClick={(e) => e.stopPropagation()}
            />
          </div>
        )}

        {/* Estimated Completion */}
        <Card className="bg-gradient-to-r from-[#457B9D] to-[#5A8FB0] text-white">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm text-white/80 mb-1">Estimated Completion</p>
              <p className="text-2xl font-bold">April 2, 2026</p>
            </div>
            <Clock size={48} className="text-white/50" />
          </div>
        </Card>
      </div>
    </DashboardLayout>
  );
}