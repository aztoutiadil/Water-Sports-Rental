<?php
class HomeController extends BaseController {
    public function index() {
        // Get some basic stats for the homepage
        $stats = $this->getPublicStats();
        
        // Render the homepage
        $this->render('home/index', [
            'stats' => $stats
        ]);
    }
    
    private function getPublicStats() {
        // Get total equipment count
        $stmt = $this->db->query("SELECT COUNT(*) FROM jet_skis WHERE status = 'available'");
        $availableJetSkis = $stmt->fetchColumn();
        
        $stmt = $this->db->query("SELECT COUNT(*) FROM tourist_boats WHERE status = 'available'");
        $availableBoats = $stmt->fetchColumn();
        
        return [
            'availableJetSkis' => $availableJetSkis,
            'availableBoats' => $availableBoats
        ];
    }
}
?> 