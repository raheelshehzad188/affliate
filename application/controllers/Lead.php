<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Lead extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model(['Lead_model', 'Affiliate_model', 'Click_model']);
    }

    // Capture Lead Form
    public function capture() {
        // Track affiliate click if affiliate ID in URL
        $affiliate_id = $this->input->get('aff');
        if ($affiliate_id) {
            // Set cookie for 30 days
            setcookie('affiliate_id', $affiliate_id, time() + (30 * 24 * 60 * 60), '/');
            // Record click
            $this->Click_model->record($affiliate_id);
        }
        
        if ($this->input->post()) {
            $data = [
                'name' => $this->input->post('name'),
                'email' => $this->input->post('email'),
                'phone' => $this->input->post('phone'),
                'location' => $this->input->post('location'),
                'prefer_date' => $this->input->post('prefer_date'),
                'detail' => $this->input->post('detail'),
                'affiliate_id' => $this->input->cookie('affiliate_id') ?: null,
                'status' => 'pending'
            ];
            
            $lead_id = $this->Lead_model->create($data);
            
            if ($lead_id) {
                // Send email notification
                $this->send_lead_notification($lead_id);
                
                // Send to HubSpot if affiliate has token
                if ($data['affiliate_id']) {
                    $affiliate = $this->Affiliate_model->get_by_id($data['affiliate_id']);
                    if ($affiliate && $affiliate->hubspot_token) {
                        $this->send_to_hubspot($data, $affiliate->hubspot_token);
                    }
                }
                
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Lead submitted successfully!']);
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Failed to submit lead']);
            }
            exit;
        }
        
        $this->load->view('lead/form');
    }

    // Send lead notification email
    private function send_lead_notification($lead_id) {
        $lead = $this->Lead_model->get_by_id($lead_id);
        
        $message = "
        <html>
        <head><title>New Lead Notification</title></head>
        <body>
            <h2>New Lead Details</h2>
            <table border='1' cellpadding='10'>
                <tr><th>Name</th><td>{$lead->name}</td></tr>
                <tr><th>Email</th><td>{$lead->email}</td></tr>
                <tr><th>Phone</th><td>{$lead->phone}</td></tr>
                <tr><th>Location</th><td>{$lead->location}</td></tr>
                <tr><th>Preferred Date</th><td>{$lead->prefer_date}</td></tr>
            </table>
        </body>
        </html>
        ";
        
        $this->load->library('email');
        $this->email->from('noreply@example.com', 'Affiliate System');
        $this->email->to('admin@example.com'); // Change this
        $this->email->subject('New Lead Notification');
        $this->email->message($message);
        $this->email->send();
    }

    // Send to HubSpot
    private function send_to_hubspot($data, $token) {
        $url = 'https://api.hubapi.com/crm/v3/objects/contacts';
        
        $leadData = [
            'properties' => [
                'email' => $data['email'],
                'firstname' => $data['name'],
                'phone' => $data['phone']
            ]
        ];
        
        if (isset($data['prefer_date']) && $data['prefer_date']) {
            $date = new DateTime($data['prefer_date']);
            $leadData['properties']['booking_date'] = $date->getTimestamp() * 1000;
        }
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($leadData));
        curl_exec($ch);
        curl_close($ch);
    }
}

