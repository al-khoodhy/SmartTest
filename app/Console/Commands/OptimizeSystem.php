<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class OptimizeSystem extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system:optimize {--force : Force optimization even in production}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Optimize the system for better performance';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting system optimization...');
        
        // Clear all caches
        $this->clearCaches();
        
        // Optimize configuration
        $this->optimizeConfig();
        
        // Optimize routes
        $this->optimizeRoutes();
        
        // Optimize views
        $this->optimizeViews();
        
        // Optimize autoloader
        $this->optimizeAutoloader();
        
        // Database optimization
        $this->optimizeDatabase();
        
        // Clear expired sessions
        $this->clearExpiredSessions();
        
        $this->info('System optimization completed successfully!');
        
        return 0;
    }
    
    /**
     * Clear all application caches
     */
    private function clearCaches()
    {
        $this->info('Clearing caches...');
        
        // Clear application cache
        Artisan::call('cache:clear');
        $this->line('✓ Application cache cleared');
        
        // Clear config cache
        Artisan::call('config:clear');
        $this->line('✓ Configuration cache cleared');
        
        // Clear route cache
        Artisan::call('route:clear');
        $this->line('✓ Route cache cleared');
        
        // Clear view cache
        Artisan::call('view:clear');
        $this->line('✓ View cache cleared');
        
        // Clear compiled services
        Artisan::call('clear-compiled');
        $this->line('✓ Compiled services cleared');
    }
    
    /**
     * Optimize configuration
     */
    private function optimizeConfig()
    {
        if (app()->environment('production') || $this->option('force')) {
            $this->info('Optimizing configuration...');
            
            Artisan::call('config:cache');
            $this->line('✓ Configuration cached');
        }
    }
    
    /**
     * Optimize routes
     */
    private function optimizeRoutes()
    {
        if (app()->environment('production') || $this->option('force')) {
            $this->info('Optimizing routes...');
            
            Artisan::call('route:cache');
            $this->line('✓ Routes cached');
        }
    }
    
    /**
     * Optimize views
     */
    private function optimizeViews()
    {
        $this->info('Optimizing views...');
        
        Artisan::call('view:cache');
        $this->line('✓ Views cached');
    }
    
    /**
     * Optimize autoloader
     */
    private function optimizeAutoloader()
    {
        if (app()->environment('production') || $this->option('force')) {
            $this->info('Optimizing autoloader...');
            
            Artisan::call('optimize');
            $this->line('✓ Autoloader optimized');
        }
    }
    
    /**
     * Optimize database
     */
    private function optimizeDatabase()
    {
        $this->info('Optimizing database...');
        
        try {
            // For SQLite, run VACUUM to optimize database file
            if (config('database.default') === 'sqlite') {
                DB::statement('VACUUM');
                $this->line('✓ SQLite database optimized');
            }
            
            // Analyze tables for better query performance
            DB::statement('ANALYZE');
            $this->line('✓ Database statistics updated');
            
        } catch (\Exception $e) {
            $this->warn('Database optimization skipped: ' . $e->getMessage());
        }
    }
    
    /**
     * Clear expired sessions
     */
    private function clearExpiredSessions()
    {
        $this->info('Cleaning up expired sessions...');
        
        try {
            // Clear expired sessions from database
            DB::table('sessions')
                ->where('last_activity', '<', now()->subHours(2)->timestamp)
                ->delete();
                
            $this->line('✓ Expired sessions cleared');
        } catch (\Exception $e) {
            $this->warn('Session cleanup skipped: ' . $e->getMessage());
        }
    }
}
