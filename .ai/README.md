# Maps2 - AI Technical Specifications & Context Map

This hidden directory (`.ai/`) acts as the modular knowledge base and technical specifications repository for the `maps2` TYPO3 extension. It prevents cluttering `AGENTS.md` (which is reserved strictly for global code-quality rules and guidelines) and protects the `Documentation/` folder from being cluttered with files that would trigger TYPO3 rst documentation build warnings.

## How to use this directory in your AI session

1. **Keep it updated:** If you make database changes, register new events, or implement complex new features, update the corresponding files under `.ai/`.
2. **Read on demand:** Do not load all these files at once. Read only the file relevant to the active task.
3. **Session Handover:** Use `.ai/session_handover.md` to pass details to future sessions if you need to pause work.

---

## Directory Index

### 1. Architecture Specs
- [routing_and_plugins.md](file:///Users/froemken/htdocs/typo3143/packages/maps2/.ai/architecture/routing_and_plugins.md): Registers plugins, entry points, and details controller endpoints.
- [database_and_models.md](file:///Users/froemken/htdocs/typo3143/packages/maps2/.ai/architecture/database_and_models.md): Database tables, models, TCA overrides, and relations.
- [backend.md](file:///Users/froemken/htdocs/typo3143/packages/maps2/.ai/architecture/backend.md): Backend-specific classes, custom page module previews, and integrations.
- [client.md](file:///Users/froemken/htdocs/typo3143/packages/maps2/.ai/architecture/client.md): Unified map provider client-request subsystem for Geocoding.
- [configuration.md](file:///Users/froemken/htdocs/typo3143/packages/maps2/.ai/architecture/configuration.md): Type-safe Extension Configuration handling and DI setup.


### 2. Feature Specs
- *Planned/Active feature specifications can be placed under `.ai/features/`.*
- Example: [open_layers.md](file:///Users/froemken/htdocs/typo3143/packages/maps2/.ai/features/open_layers.md) (if implementing OpenLayers)

### 3. Active Progress & Tasks
- [session_handover.md](file:///Users/froemken/htdocs/typo3143/packages/maps2/.ai/session_handover.md): Contains current focus, tasks, and state-of-play for the active developer context.
