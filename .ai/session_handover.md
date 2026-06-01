# Session Handover & Active Context

This file serves as the active progress and task board for the AI agent. At the end of every coding session, this file should be updated with the latest context.

---

## 1. Current State of Play
- **Code Guidelines File (`AGENTS.md`):** Untouched and clean at 74 lines. Focusing strictly on strict types, constructor property promotion, return types, testing framework rules, and PSR standards.
- **Hidden AI Directory (`.ai/`):** Successfully created and initialized to serve as the modular extension knowledge base.
- **Initial Specs Created:**
  - `routing_and_plugins.md`: Documents Extbase plugins, actions, cache configurations, and form engine registrations.
  - `database_and_models.md`: Documents tables, domain models, and TCA override paths.
  - `client.md`: Documents the streamlined Map Client/Request subsystem (Google Maps and OpenStreetMap integrations).
  - `configuration.md`: Documents the type-safe, final readonly `ExtConf` value object, its default values, dynamic factory creation, and robust test suite.

---

## 2. Active Goals & Backlog
* **Goal 1:** Continue standard development of the `maps2` extension (refactorings, new features, or TYPO3 upgrade adjustments).
* **Goal 2:** As features are developed (e.g., frontend OpenLayers integrations, geocoding endpoints, hook callbacks), document their specific flows and logic inside `.ai/features/` or `.ai/architecture/`.

---

## 3. Handover Instruction for the Next AI Session
> *"We have created a hidden `.ai/` directory in the root of the `maps2` extension containing technical specifications. Read `.ai/README.md` to get an index of files, and read the relevant specs from `.ai/architecture/` depending on your current task. Keep these specification files updated as you implement new features."*
