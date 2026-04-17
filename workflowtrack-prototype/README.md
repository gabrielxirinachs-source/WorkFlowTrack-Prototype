# WorkFlowTrack Prototype

A self-contained PHP prototype that demonstrates one complete user journey from start to finish:

1. Login as **Project Manager**
2. Review the **Dashboard**
3. **Create and assign a task**
4. Logout and login as **Team Member**
5. **Update task status** from `Pending -> In Progress -> Done`
6. Open **Task History** to verify the audit trail
7. Open **Notifications** to verify assignment and status events

## Tech Stack

- PHP 8+
- HTML/CSS
- Session-based authentication
- JSON file persistence (no database setup required)

## Demo Accounts

- Project Manager: `pmaria / demo123`
- Team Member: `tjohnson / demo123`
- System Administrator: `admin1 / demo123`
- Department Management: `dept1 / demo123`

## Run the Prototype

From the project folder:

```bash
php -S localhost:8000
```

Then open:

```text
http://localhost:8000
```

## Implemented Functional Components

- Authentication and role-aware access
- Dashboard overview with KPI tiles
- Task creation and assignment
- Workflow tracking with guarded lifecycle transitions
- Audit trail / task history
- Notification center

## Implemented Middleware Components

- Session auth guard
- Role authorization guard
- Task validation middleware
- Workflow transition rules
- Audit logging on create/update actions

## Implemented UI Components

- Login screen
- Dashboard
- Create Task form
- Workflow Tracking screen
- Task History screen
- Notification Center

## Notes

- Data is stored locally in `data/*.json`
- The prototype is intentionally lightweight so it is easy to run and demo
- The administrator and department roles are included for role-based navigation coverage, but the main end-to-end journey centers on the Project Manager and Team Member flow
