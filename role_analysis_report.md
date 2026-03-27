Module One: Information & Awareness Management
Role Breakdown: Permissions, Functions, and Features
Based on the thorough analysis of Module One (Campaigns, Awareness Engagements, and Volunteer Tips) and the structure of the Addis Ababa City Administration (City -> Sub-City -> Woreda -> Block -> Citizen/Organization), here is the comprehensive breakdown of how Permissions, Functions, and Features are mapped across the 5 core roles in your system.

1. Super Admin (የበላይ አስተዳዳሪ) & 2. Admin (አስተዳዳሪ)
At the City Administration or high-level Code Enforcement Bureau level.

Permissions (Spatie): manage_users, manage_roles (Super Admin), manage_settings, create_campaigns, view_campaigns, edit_campaigns, delete_campaigns, create_engagements, view_engagements, edit_engagements, approve_engagements, reject_engagements, submit_tips, view_tips, verify_tips, take_action_on_tips, view_all_reports.

Role Functions:

System Oversight: Complete visibility into all data across all Sub-Cities (ክፍለ ከተማ) and Woredas (ወረዳ).
Strategic Campaign Planning: Responsible for creating the overarching awareness campaigns (e.g., city-wide hygiene awareness, illegal construction prevention).
Global Monitoring: Views all engagement logs, volunteer tips, and enforcement actions across the entire city to measure overall compliance and performance.
Features (Module One):

Campaign Management: Can create, edit, delete, and define the scope of new campaigns (
CampaignResource
). Sets target Sub-City/Woreda, category (House-to-House, Coffee Ceremony, Organization), and timeframes.
Global Tables: Unrestricted query access in 
AwarenessEngagementResource
 and 
VolunteerTipResource
 to see all logs.
(Note: While Admins have the approve_engagements and verify_tips permissions, the UI logic currently delegates the actual action buttons (approve, reject, verify) specific to the woreda_coordinator role for local accountability).
3. Woreda Coordinator (የወረዳ አስተባባሪ)
At the Woreda (ወረዳ) level. Acts as the bridge between execution (Paramilitary) and enforcement (Officer).

Permissions (Spatie): view_campaigns, create_engagements, view_engagements, edit_engagements, approve_engagements, reject_engagements, view_tips, verify_tips, view_reports.

Role Functions:

Local Management: Manages and validates all awareness and enforcement data specifically within their assigned Woreda.
Paramilitary Supervision: Reviews the engagement logs (data collection) submitted by the Paramilitary team on the ground.
Tip Triage: Filters and verifies tips from citizens/volunteers before they are escalated to enforcement officers.
Features (Module One):

Scoped Dashboard & Tables: In 
AwarenessEngagementResource
, they only see records where woreda_id matches their own, and only records that are NOT in draft status (i.e., submitted by Paramilitary).
Approval Workflow (Engagements): Has access to the Approve and Reject actions on Engagement Logs. If a log is inaccurate, they can reject it with a mandatory rejection_note.
Tip Verification: Has access to the Verify action on 
VolunteerTipResource
. Changes status from pending to verified, making it visible to Officers for enforcement.
4. Paramilitary (የደንብ አስከባሪ ኃይል / የመስክ ሠራተኛ)
At the Block (ብሎክ) and Citizen level. The "boots on the ground" educators and data collectors.

Permissions (Spatie): view_campaigns, create_engagements, view_engagements, edit_engagements, submit_tips, view_tips.

Role Functions:

Execution: Carries out the active campaigns (House-to-House, Coffee Ceremonies, Organization meetings) as defined by Admins.
Data Entry (Offline/Field): Logs specific details about citizens, block numbers, and violation types discussed during awareness sessions.
Information Gathering: Submits initial reports (tips) about ongoing code violations they observe or hear about during their citizen engagements.
Features (Module One):

Self-Scoped Data: In 
AwarenessEngagementResource
, they can only see their own logs (created_by === auth()->id()).
Draft & Submit Workflow: Creates Engagement Logs in draft status. Has exclusive access to the Submit action to send the log to the Woreda Coordinator for approval.
Contextual Forms: Uses dynamic forms to record citizen details (for House-to-House) or headcount/attendees (for Coffee Ceremonies/Organizations), specifically logging the violation_type discussed.
5. Officer (የደንብ መኮንን)
Authorized enforcement personnel representing the administration.

Permissions (Spatie): view_campaigns, view_engagements, view_tips, take_action_on_tips, view_reports, view_complaints, manage_complaints.

Role Functions:

Action & Enforcement: Acts purely on verified data. They do not do the initial data entry or awareness campaigns; they handle the disciplinary/legal consequences.
Tip Resolution: Reviews verified tips and determines the appropriate legal or financial action against the code violation.
Features (Module One):

Filtered Tip View: In 
VolunteerTipResource
, the query is scoped via forOfficer(), meaning they only see tips that have been verified by the Woreda Coordinator.
Take Action Interface: Exclusive access to the Log Action / እርምጃ ይዝገቡ feature. Can select an enforcement action (formal_warning, financial_penalty, asset_confiscation, legal_referral), log notes, and finalize the resolution of a violation.
Summary of the Flow in the Addis Ababa Context:
Admin creates a Campaign targeting a specific Sub-City/Woreda (e.g., Illegal Trading Prevention in Bole).
Paramilitary agents go out to the Blocks, conduct House-to-House visits, log the citizens educated, and Submit the engagement log.
Woreda Coordinator reviews the log and Approves it, validating the Paramilitary's work.
If a citizen reports a severe violation during the visit, a Volunteer Tip is created.
The Woreda Coordinator reviews the Tip. If valid, they Verify it.
The Officer sees the Verified Tip and uses the system to Log Action, officially issuing a penalty or confiscation.