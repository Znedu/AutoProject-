import { useState } from 'react';
import { DashboardLayout } from '../../components/DashboardLayout';
import { StatCard } from '../../components/Card';
import { Wrench, Clock, CheckCircle, AlertCircle, Camera, X, Upload, Image as ImageIcon } from 'lucide-react';
import { Link } from 'react-router';
import { Button } from '../../components/Button';
import { Card } from '../../components/Card';
import { StatusBadge } from '../../components/StatusBadge';
import { TextArea } from '../../components/FormInputs';
import { showToast } from '../../utils/toast';

export default function MechanicDashboard() {
  const [showUpdateModal, setShowUpdateModal] = useState(false);
  const [selectedJob, setSelectedJob] = useState<number | null>(null);
  const [updateNote, setUpdateNote] = useState('');
  const [selectedPhotos, setSelectedPhotos] = useState<File[]>([]);

  const handlePhotoUpload = (e: React.ChangeEvent<HTMLInputElement>) => {
    const files = Array.from(e.target.files || []);
    if (files.length + selectedPhotos.length > 5) {
      showToast.error('Maximum 5 photos allowed per update');
      return;
    }
    setSelectedPhotos([...selectedPhotos, ...files]);
  };

  const removePhoto = (index: number) => {
    setSelectedPhotos(selectedPhotos.filter((_, i) => i !== index));
  };

  const handleUpdateProgress = (jobId: number) => {
    setSelectedJob(jobId);
    setShowUpdateModal(true);
  };

  const handleSubmitUpdate = () => {
    if (!updateNote.trim() && selectedPhotos.length === 0) {
      showToast.error('Please add a note or at least one photo');
      return;
    }

    showToast.success(`Progress update sent! ${selectedPhotos.length} photo(s) attached. Customer will be notified.`);
    setShowUpdateModal(false);
    setUpdateNote('');
    setSelectedPhotos([]);
    setSelectedJob(null);
  };
  const assignedJobs = [
    {
      id: 1,
      customer: 'Juan Dela Cruz',
      service: 'Paint Job',
      vehicle: 'Toyota Supra 2021',
      status: 'in-progress' as const,
      priority: 'High',
    },
    {
      id: 2,
      customer: 'Maria Santos',
      service: 'Engine Customization',
      vehicle: 'Honda Civic 2020',
      status: 'pending' as const,
      priority: 'Medium',
    },
  ];

  return (
    <DashboardLayout role="mechanic">
      <div className="space-y-8">
        {/* Header */}
        <div>
          <h1 className="text-3xl font-bold text-gray-900 dark:text-white mb-2">Mechanic Dashboard</h1>
          <p className="text-gray-600 dark:text-gray-400">Manage your assigned jobs and service updates.</p>
        </div>

        {/* Stats */}
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          <StatCard
            title="Assigned Jobs"
            value="2"
            icon={<Wrench size={24} />}
            color="blue"
          />
          <StatCard
            title="In Progress"
            value="1"
            icon={<Clock size={24} />}
            color="red"
          />
          <StatCard
            title="Completed Today"
            value="3"
            icon={<CheckCircle size={24} />}
            color="green"
          />
          <StatCard
            title="Pending Start"
            value="1"
            icon={<AlertCircle size={24} />}
            color="charcoal"
          />
        </div>

        {/* Quick Actions */}
        <div className="bg-white dark:bg-[#151515]/60 border border-gray-200 dark:border-gray-800 rounded-lg shadow-md p-6">
          <h2 className="text-xl font-bold text-gray-900 dark:text-white mb-4">Quick Actions</h2>
          <div className="flex flex-wrap gap-4 mb-4">
            <Link to="/mechanic/jobs">
              <Button variant="accent">View All Jobs</Button>
            </Link>
            <Link to="/mechanic/notes">
              <Button variant="secondary">Add Service Note</Button>
            </Link>
          </div>
          <div className="p-3 bg-blue-50 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-800 rounded-lg">
            <div className="flex items-start gap-2">
              <Camera size={16} className="text-blue-600 dark:text-blue-400 mt-0.5 flex-shrink-0" />
              <p className="text-sm text-blue-800 dark:text-blue-200">
                <strong>Photo Updates:</strong> Click "Update Progress" on any in-progress job to send photos and updates to customers. This builds trust and keeps them informed!
              </p>
            </div>
          </div>
        </div>

        {/* Current Jobs */}
        <Card>
          <h2 className="text-xl font-bold text-gray-900 dark:text-white mb-4">Current Assigned Jobs</h2>
          <div className="space-y-4">
            {assignedJobs.map((job) => (
              <div key={job.id} className="border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-[#0B0B0B]/40 rounded-lg p-4">
                <div className="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                  <div className="flex-1">
                    <div className="flex items-center gap-3 mb-2">
                      <h3 className="font-bold text-gray-900 dark:text-white">{job.service}</h3>
                      <StatusBadge status={job.status}>
                        {job.status === 'in-progress' ? 'In Progress' : 'Pending Start'}
                      </StatusBadge>
                      <span className={`px-2 py-1 rounded text-xs font-medium ${
                        job.priority === 'High' ? 'bg-red-100 dark:bg-red-500/20 text-red-800 dark:text-red-300' :
                        job.priority === 'Medium' ? 'bg-yellow-100 dark:bg-yellow-500/20 text-yellow-800 dark:text-yellow-300' :
                        'bg-blue-100 dark:bg-blue-500/20 text-blue-800 dark:text-blue-300'
                      }`}>
                        {job.priority} Priority
                      </span>
                    </div>
                    <p className="text-sm text-gray-600 dark:text-gray-400">Customer: {job.customer}</p>
                    <p className="text-sm text-gray-600 dark:text-gray-400">Vehicle: {job.vehicle}</p>
                  </div>
                  <div className="flex gap-2">
                    {job.status === 'pending' && (
                      <Button variant="accent" size="sm">Start Job</Button>
                    )}
                    {job.status === 'in-progress' && (
                      <>
                        <Button
                          variant="secondary"
                          size="sm"
                          onClick={() => handleUpdateProgress(job.id)}
                        >
                          <Camera size={16} className="mr-1" />
                          Update Progress
                        </Button>
                        <Button variant="outline" size="sm">Pause</Button>
                      </>
                    )}
                  </div>
                </div>
              </div>
            ))}
          </div>
        </Card>

        {/* Recent Activity */}
        <Card>
          <h2 className="text-xl font-bold text-gray-900 dark:text-white mb-4">Recent Activity</h2>
          <div className="space-y-3">
            <div className="flex items-start gap-3 pb-3 border-b border-gray-200 dark:border-gray-700">
              <div className="w-2 h-2 bg-green-500 rounded-full mt-2"></div>
              <div>
                <p className="text-gray-900 dark:text-white font-medium">Completed: Turbo Installation</p>
                <p className="text-sm text-gray-600 dark:text-gray-400">Subaru WRX 2022 - 2 hours ago</p>
              </div>
            </div>
            <div className="flex items-start gap-3 pb-3 border-b border-gray-200 dark:border-gray-700">
              <div className="w-2 h-2 bg-blue-500 rounded-full mt-2"></div>
              <div>
                <p className="text-gray-900 dark:text-white font-medium">Updated progress: Paint Job</p>
                <p className="text-sm text-gray-600 dark:text-gray-400">Toyota Supra 2021 - 3 hours ago</p>
              </div>
            </div>
            <div className="flex items-start gap-3">
              <div className="w-2 h-2 bg-yellow-500 rounded-full mt-2"></div>
              <div>
                <p className="text-gray-900 dark:text-white font-medium">Started: Paint Job</p>
                <p className="text-sm text-gray-600 dark:text-gray-400">Toyota Supra 2021 - 1 day ago</p>
              </div>
            </div>
          </div>
        </Card>

        {/* Update Progress Modal */}
        {showUpdateModal && (
          <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <Card className="max-w-2xl w-full max-h-[90vh] overflow-y-auto">
              <div className="flex items-center justify-between mb-4">
                <h2 className="text-2xl font-bold text-gray-900 dark:text-white">Update Progress</h2>
                <button
                  onClick={() => {
                    setShowUpdateModal(false);
                    setUpdateNote('');
                    setSelectedPhotos([]);
                    setSelectedJob(null);
                  }}
                  className="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                >
                  <X size={24} />
                </button>
              </div>

              <div className="space-y-4">
                {/* Note Input */}
                <div>
                  <label className="block text-sm font-medium mb-2 text-gray-900 dark:text-white">
                    Progress Update Note *
                  </label>
                  <TextArea
                    value={updateNote}
                    onChange={(e) => setUpdateNote(e.target.value)}
                    placeholder="Describe the current progress, what's been completed, and next steps..."
                    rows={4}
                  />
                  <p className="text-xs text-gray-600 dark:text-gray-400 mt-1">
                    This note will be visible to the customer
                  </p>
                </div>

                {/* Photo Upload */}
                <div>
                  <label className="block text-sm font-medium mb-2 text-gray-900 dark:text-white">
                    Progress Photos (Optional - Max 5)
                  </label>

                  {/* Upload Button */}
                  <div className="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-6 text-center">
                    <input
                      type="file"
                      id="photo-upload"
                      accept="image/*"
                      multiple
                      onChange={handlePhotoUpload}
                      className="hidden"
                    />
                    <label
                      htmlFor="photo-upload"
                      className="cursor-pointer inline-flex flex-col items-center"
                    >
                      <Upload size={48} className="text-gray-400 dark:text-gray-500 mb-2" />
                      <span className="text-sm font-medium text-gray-700 dark:text-gray-300">
                        Click to upload photos
                      </span>
                      <span className="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        JPG, PNG up to 10MB each
                      </span>
                    </label>
                  </div>

                  {/* Photo Preview */}
                  {selectedPhotos.length > 0 && (
                    <div className="mt-4 grid grid-cols-2 sm:grid-cols-3 gap-3">
                      {selectedPhotos.map((photo, index) => (
                        <div key={index} className="relative group">
                          <div className="aspect-square bg-gray-100 dark:bg-gray-800 rounded-lg overflow-hidden">
                            <img
                              src={URL.createObjectURL(photo)}
                              alt={`Preview ${index + 1}`}
                              className="w-full h-full object-cover"
                            />
                          </div>
                          <button
                            onClick={() => removePhoto(index)}
                            className="absolute top-2 right-2 bg-red-600 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity"
                          >
                            <X size={16} />
                          </button>
                          <p className="text-xs text-gray-600 dark:text-gray-400 mt-1 truncate">
                            {photo.name}
                          </p>
                        </div>
                      ))}
                    </div>
                  )}

                  <div className="mt-3 p-3 bg-blue-50 dark:bg-blue-950/30 rounded-lg border border-blue-200 dark:border-blue-800">
                    <div className="flex items-start gap-2">
                      <Camera size={16} className="text-blue-600 dark:text-blue-400 mt-0.5 flex-shrink-0" />
                      <p className="text-xs text-blue-800 dark:text-blue-200">
                        <strong>Tip:</strong> Include photos showing different angles, close-ups of work progress, and any issues found. This helps keep customers informed and builds trust.
                      </p>
                    </div>
                  </div>
                </div>

                {/* Action Buttons */}
                <div className="flex gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                  <Button
                    variant="accent"
                    onClick={handleSubmitUpdate}
                    className="bg-green-600 hover:bg-green-700 text-white"
                  >
                    <ImageIcon size={16} className="mr-2" />
                    Send Update
                  </Button>
                  <Button
                    variant="outline"
                    onClick={() => {
                      setShowUpdateModal(false);
                      setUpdateNote('');
                      setSelectedPhotos([]);
                      setSelectedJob(null);
                    }}
                    className="bg-red-600 hover:bg-red-700 text-white border-red-600"
                  >
                    Cancel
                  </Button>
                </div>
              </div>
            </Card>
          </div>
        )}
      </div>
    </DashboardLayout>
  );
}