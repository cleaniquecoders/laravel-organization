# Documentation

Welcome to the Laravel Organization package documentation. This package provides organization-based
multi-tenancy with user roles, automatic data scoping, and extensive configuration options.

## Overview

The documentation is organized into four main sections covering architecture, development, usage, and troubleshooting.

## Documentation Structure

### [01. Architecture](01-architecture/README.md)

System design, SOLID contracts, and event-driven architecture.

- [Overview](01-architecture/01-overview.md) - Features and capabilities
- [Contracts](01-architecture/02-contracts.md) - SOLID interfaces and dependency injection
- [Events](01-architecture/03-events.md) - Domain events and listeners (14 events)

### [02. Development](02-development/README.md)

Installation, setup, and configuration guides.

- [Installation](02-development/01-installation.md) - Package installation and setup
- [Configuration](02-development/02-configuration.md) - All configuration options

### [03. Usage](03-usage/README.md)

Day-to-day usage, authorization, and component guides.

- [Usage Guide](03-usage/01-usage.md) - Working with organizations
- [Authorization & Policies](03-usage/02-authorization-and-policies.md) - Permission system
- [Components & Actions](03-usage/03-components-and-actions.md) - Livewire components and action classes
- [Invitations](03-usage/04-invitations.md) - Organization invitation system
- [Organization Switching](03-usage/05-organization-switching.md) - Hybrid session/database approach

### [04. Troubleshooting](04-troubleshooting/README.md)

Known issues and solutions.

- [Memory Exhaustion Fix](04-troubleshooting/01-memory-exhaustion-fix.md) - Infinite loop prevention

## Quick Start

New to the package? Start with [Installation](02-development/01-installation.md).

## Finding Information

- **Architecture/Concepts**: Check the [Architecture](01-architecture/README.md) section
- **How-to Guides**: Check the [Usage](03-usage/README.md) section
- **Configuration Reference**: Check the [Development](02-development/README.md) section
- **Known Issues**: Check the [Troubleshooting](04-troubleshooting/README.md) section
