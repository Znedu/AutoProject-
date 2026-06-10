import { DashboardLayout } from '../../components/DashboardLayout';
import { Card } from '../../components/Card';
import { Button } from '../../components/Button';
import { StatusBadge } from '../../components/StatusBadge';
import { Wrench, Play, Pause, CheckCircle, Clock } from 'lucide-react';
import { showToast } from '../../utils/toast';

export default function AssignedJobs() {
  const jobs = [
    {
      id: 1,
      customer: 'Juan Dela Cruz',
      contactNumber: '+63 912 345 6789',
      service: 'Paint Job',
      vehicle: 'Toyota Supra 2021',
      plateNumber: 'XYZ 5678',
      status: 'in-progress' as const,
      progress: 65,
      startDate: 'March 29, 2026',
      estimatedCompletion: 'April 2, 2026',
      priority: 'High',
    },
    {
      id: 2,
      customer: 'Maria Santos',
      contactNumber: '+63 917 888 9999',
      service: 'Engine Customization',
      vehicle: 'Honda Civic 2020',
      plateNumber: 'ABC 1234',
      status: 'pending' as const,
      progress: 0,
      startDate: 'April 5, 2026',
      estimatedCompletion: 'April 12, 2026',
      priority: 'Medium',
    },
    {
      id: 3,
      customer: 'Pedro Rodriguez',
      contactNumber: '+63 923 111 2222',
      service: 'Turbo Installation',
      vehicle: 'Subaru WRX 2022',
      plateNumber: 'GHI 3456',
      status: 'completed' as const,
      progress: 100,
      startDate: 'March 18, 2026',
      estimatedCompletion: 'March 20, 2026',
      priority: 'High',
    },
  ];

  const handleStartJob = (jobId: number) => {
    showToast.success(`Job ${jobId} started!`);
  };

  const handlePauseJob = (jobId: number) => {
    showToast.info(`Job ${jobId} paused!`);
  };

  const handleCompleteJob = (jobId: number) => {
    showToast.success(`Job ${jobId} marked as complete!`);
  };

  return (
    <DashboardLayout role="mechanic">
      <div className="space-y-6">
        {/* Header */}
        <div>
          <h1 className="text-3xl font-bold text-[#1F2937] mb-2">Assigned Jobs</h1>
          <p className="text-gray-600">Manage and update your assigned service jobs.</p>
        </div>

        {/* Filters */}
        <Card>
          <div className="flex flex-wrap gap-2">
            <Button variant="primary" size="sm">All Jobs</Button>
            <Button variant="ghost" size="sm">Pending</Button>
            <Button variant="ghost" size="sm">In Progress</Button>
            <Button variant="ghost" size="sm">Completed</Button>
          </div>
        </Card>

        {/* Jobs List */}
        <div className="space-y-4">
          {jobs.map((job) => (
            <Card key={job.id}>
              <div className="space-y-4">
                {/* Header */}
                <div className="flex flex-col lg:flex-row lg:items-start justify-between gap-4">
                  <div className="flex-1">
                    <div className="flex flex-wrap items-center gap-3 mb-3">
                      <h3 className="text-xl font-bold text-[#1F2937]">{job.service}</h3>
                      <StatusBadge status={job.status}>
                        {job.status === 'in-progress' ? 'In Progress' : 
                         job.status === 'pending' ? 'Pending Start' : 'Completed'}
                      </StatusBadge>
                      <span className={`px-2 py-1 rounded text-xs font-medium ${
                        job.priority === 'High' ? 'bg-red-100 text-red-800' :
                        job.priority === 'Medium' ? 'bg-yellow-100 text-yellow-800' :
                        'bg-blue-100 text-blue-800'
                      }`}>
                        {job.priority} Priority
                      </span>
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                      <div>
                        <p className="text-gray-600 mb-1">Customer</p>
                        <p className="font-medium text-[#1F2937]">{job.customer}</p>
                        <p className="text-gray-600">{job.contactNumber}</p>
                      </div>
                      <div>
                        <p className="text-gray-600 mb-1">Vehicle</p>
                        <p className="font-medium text-[#1F2937]">{job.vehicle}</p>
                        <p className="text-gray-600">{job.plateNumber}</p>
                      </div>
                      <div>
                        <p className="text-gray-600 mb-1">Start Date</p>
                        <p className="font-medium text-[#1F2937]">{job.startDate}</p>
                      </div>
                      <div>
                        <p className="text-gray-600 mb-1">Est. Completion</p>
                        <p className="font-medium text-[#1F2937]">{job.estimatedCompletion}</p>
                      </div>
                    </div>
                  </div>
                </div>

                {/* Progress Bar */}
                {job.status !== 'pending' && (
                  <div className="space-y-2">
                    <div className="flex justify-between text-sm">
                      <span className="text-gray-600">Progress</span>
                      <span className="font-medium text-[#1F2937]">{job.progress}%</span>
                    </div>
                    <div className="w-full bg-gray-200 rounded-full h-3">
                      <div
                        className={`h-3 rounded-full transition-all ${
                          job.status === 'completed' ? 'bg-green-500' : 'bg-[#E63946]'
                        }`}
                        style={{ width: `${job.progress}%` }}
                      />
                    </div>
                  </div>
                )}

                {/* Action Buttons */}
                <div className="flex flex-wrap gap-3 pt-4 border-t">
                  {job.status === 'pending' && (
                    <Button className="text-[#fefdfd] text-[#fefbfb] text-[#fdf8f8] text-[#fbf0f0] text-[#faecec] text-[#fae8e8] text-[#f6dbdb] text-[#f4d7d7] text-[#f1cece] text-[#ecc5c5] text-[#e7bbbb] text-[#e2b0b0] text-[#e1aeae] text-[#daa3a3] text-[#d49d9d] text-[#ce9696] text-[#c68e8e] text-[#c28989] text-[#c18787] text-[#be8484] text-[#bc8282] text-[#bb8080] text-[#bb8080] text-[#ba7f7f] text-[#b77b7b] text-[#b57979] text-[#b37777] text-[#af7272] text-[#ad7070] text-[#aa6d6d] text-[#a86b6b] text-[#a66969] text-[#a36666] text-[#9d6161] text-[#9a5d5d] text-[#965959] text-[#945757] text-[#925555] text-[#905454] text-[#8f5252] text-[#8d5151] text-[#894d4d] text-[#854a4a] text-[#804646] text-[#7c4343] text-[#794040] text-[#784040] text-[#783f3f] text-[#773f3f] text-[#763e3e] text-[#753d3d] text-[#743d3d] text-[#733c3c] text-[#723c3c] text-[#6e3a3a] text-[#6a3939] text-[#663838] text-[#5f3636] text-[#5d3535] text-[#553333] text-[#4e3030] text-[#4b2f2f] text-[#4a2e2e] text-[#422b2b] text-[#382727] text-[#352525] text-[#2f2222] text-[#291f1f] text-[#241b1b] text-[#1f1818] text-[#1c1616] text-[#1b1616] text-[#1a1515] text-[#191414] text-[#181313] text-[#161212] text-[#151111] text-[#131010] text-[#131010] text-[#120f0f] text-[#110f0f] text-[#100e0e] text-[#0e0c0c] text-[#0d0c0c] text-[#0c0b0b] text-[#0b0a0a] text-[#0b0a0a] text-[#0a0909] text-[#0a0909] text-[#090808] text-[#070707] text-[#060606] text-[#040404] text-[#030303] text-[#030303] text-[#030303]"
                      variant="accent"
                      size="sm"
                      onClick={() => handleStartJob(job.id)}
                    >
                      <Play size={16} className="mr-2" />
                      Start Job
                    </Button>
                  )}
                  {job.status === 'in-progress' && (
                    <>
                      <Button
                        variant="secondary"
                        size="sm"
                        onClick={() => handlePauseJob(job.id)}
                      >
                        <Pause size={16} className="mr-2" />
                        Pause Job
                      </Button>
                      <Button
                        variant="accent"
                        size="sm"
                        onClick={() => handleCompleteJob(job.id)}
                      >
                        <CheckCircle size={16} className="mr-2" />
                        Complete Job
                      </Button>
                    </>
                  )}
                  <Button variant="outline" size="sm">
                    <Clock size={16} className="mr-2" />
                    Update Progress
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