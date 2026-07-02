<?php

namespace Database\Seeders;

use App\Enums\RoleSlug;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            ServiceSeeder::class,
            BookingStatusSeeder::class,
            JobStatusSeeder::class,
            TicketStatusSeeder::class,
            AdminSeeder::class,
            BusinessSettingsSeeder::class,
        ]);

        $roles = Role::query()->pluck('id', 'slug');

        $users = [
            [
                'name' => 'Carlos Customer',
                'email' => 'customer@gmail.com',
                'phone' => '+63 915 222 3333',
                'role_id' => $roles[RoleSlug::Customer->value],
                'status' => User::STATUS_ACTIVE,
                'password' => 'demo123',
            ],
            [
                'name' => 'Sarah Staff',
                'email' => 'staff@gmail.com',
                'phone' => '+63 923 333 4444',
                'role_id' => $roles[RoleSlug::Staff->value],
                'status' => User::STATUS_ACTIVE,
                'password' => 'demo123',
            ],
            [
                'name' => 'John Mechanic',
                'email' => 'res.json()) .then(data => { if (data.success) { job.status = 'in-progress'; showToast.success('Job #' + job.id + ' started!'); } else { showToast.error('Failed to start job.'); } }) .catch(err => showToast.error('An error occurred.')); }, handlePauseJob(job) { fetch('/mechanic/jobs/' + job.id + '/pause', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') } }) .then(res => res.json()) .then(data => { if (data.success) { job.status = 'pending'; showToast.info('Job #' + job.id + ' paused!'); } else { showToast.error('Failed to pause job.'); } }) .catch(err => showToast.error('An error occurred.')); }, handleUpdateProgress(jobId) { this.selectedJobId = jobId; this.showUpdateModal = true; }, handlePhotoUpload(e) { const files = Array.from(e.target.files || []); if (files.length + this.selectedPhotos.length > 5) { showToast.error('Maximum 5 photos allowed per update'); return; } files.forEach(file => { this.selectedPhotos.push({ name: file.name, url: URL.createObjectURL(file) }); }); }, removePhoto(index) { this.selectedPhotos = this.selectedPhotos.filter((_, i) => i !== index); }, handleSubmitUpdate() { if (!this.updateNote.trim()) { showToast.error('Please add a note'); return; } fetch('/mechanic/notes', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }, body: JSON.stringify({ jobId: this.selectedJobId, note: this.updateNote }) }) .then(res => res.json()) .then(data => { if (data.success) { showToast.success('Progress update note saved successfully! Customer will be notified.'); this.closeModal(); } else { showToast.error('Failed to save progress update: ' + (data.error || 'Unknown error')); } }) .catch(err => showToast.error('An error occurred.')); }, closeModal() { this.showUpdateModal = false; this.updateNote = ''; this.selectedPhotos = []; this.selectedJobId = null; } }" class="space-y-8 animate-fade-in" >',
                'phone' => '+63 920 111 2222',
                'role_id' => $roles[RoleSlug::Mechanic->value],
                'status' => User::STATUS_ACTIVE,
                'password' => 'demo123',
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                $userData,
            );
        }
    }
}
