<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Customer;
use App\Models\WalletRecharge;
use App\Models\PopularPlace;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FixAllImagePaths extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fix-all-image-paths';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix image paths in the database for profiles, wallet recharges, and popular places';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to fix image paths...');
        
        // Fix customer profile pictures
        $this->fixCustomerProfilePictures();
        
        // Fix wallet recharge photos
        $this->fixWalletRechargePictures();
        
        // Fix popular place images
        $this->fixPopularPlacesImages();
        
        $this->info('Image path fixing complete');
    }
    
    /**
     * Fix customer profile pictures
     */
    private function fixCustomerProfilePictures()
    {
        // Get all customers with pictures
        $customers = Customer::whereNotNull('picture')->get();
        
        $this->info("Found {$customers->count()} customers with pictures to fix");
        
        foreach ($customers as $customer) {
            $originalPath = $customer->picture;
            
            // If path starts with /storage/, it needs fixing
            if (Str::startsWith($originalPath, '/storage/')) {
                // Get just the filename
                $filename = basename($originalPath);
                
                // Check if file exists in the old location
                $oldStoragePath = 'public' . $originalPath;
                
                if (Storage::exists($oldStoragePath)) {
                    // Create new path
                    $newPath = 'profile/' . $filename;
                    
                    // Copy file to new location
                    Storage::copy($oldStoragePath, 'public/' . $newPath);
                    
                    // Update database record
                    $customer->picture = $newPath;
                    $customer->save();
                    
                    $this->info("Fixed path for customer {$customer->id}: {$originalPath} -> {$newPath}");
                } else {
                    $this->warn("File not found for customer {$customer->id}: {$oldStoragePath}");
                }
            } else {
                $this->info("Path already correct for customer {$customer->id}: {$originalPath}");
            }
        }
    }
    
    /**
     * Fix wallet recharge pictures
     */
    private function fixWalletRechargePictures()
    {
        // Get all recharges with photos
        $recharges = WalletRecharge::whereNotNull('photo')->get();
        
        $this->info("Found {$recharges->count()} wallet recharges with photos to fix");
        
        foreach ($recharges as $recharge) {
            $originalPath = $recharge->photo;
            
            // If path starts with /storage/, it needs fixing
            if (Str::startsWith($originalPath, '/storage/')) {
                // Get just the filename
                $filename = basename($originalPath);
                
                // Check if file exists in the old location
                $oldStoragePath = 'public' . $originalPath;
                
                if (Storage::exists($oldStoragePath)) {
                    // Create new path
                    $newPath = 'recharges/' . $filename;
                    
                    // Copy file to new location
                    Storage::copy($oldStoragePath, 'public/' . $newPath);
                    
                    // Update database record
                    $recharge->photo = $newPath;
                    $recharge->save();
                    
                    $this->info("Fixed path for recharge {$recharge->id}: {$originalPath} -> {$newPath}");
                } else {
                    $this->warn("File not found for recharge {$recharge->id}: {$oldStoragePath}");
                }
            } else {
                $this->info("Path already correct for recharge {$recharge->id}: {$originalPath}");
            }
        }
    }
    
    /**
     * Fix popular places images
     */
    private function fixPopularPlacesImages()
    {
        // Get all popular places with images
        $places = PopularPlace::whereNotNull('images')->get();
        
        $this->info("Found {$places->count()} popular places with images to fix");
        
        foreach ($places as $place) {
            $changed = false;
            $images = $place->images;
            
            if (!is_array($images)) {
                continue;
            }
            
            foreach ($images as $key => $path) {
                // If path starts with /storage/, it needs fixing
                if (Str::startsWith($path, '/storage/')) {
                    // Get just the filename
                    $filename = basename($path);
                    
                    // Check if file exists in the old location
                    $oldStoragePath = 'public' . $path;
                    
                    if (Storage::exists($oldStoragePath)) {
                        // Create new path
                        $newPath = 'places/' . $filename;
                        
                        // Copy file to new location
                        Storage::copy($oldStoragePath, 'public/' . $newPath);
                        
                        // Update array element
                        $images[$key] = $newPath;
                        $changed = true;
                        
                        $this->info("Fixed path for popular place {$place->id} image: {$path} -> {$newPath}");
                    } else {
                        $this->warn("File not found for popular place {$place->id} image: {$oldStoragePath}");
                    }
                }
            }
            
            // Save changes if any were made
            if ($changed) {
                $place->images = $images;
                $place->save();
            } else {
                $this->info("Paths already correct for popular place {$place->id}");
            }
        }
    }
}
