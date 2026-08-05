# Decisions

This file records decisions that should remain stable unless the user explicitly changes them.

## D001: Laravel and MySQL

**Decision:** Use Laravel 13, PHP 8.5, and MySQL 8.4 through Laravel Sail.

**Reason:** This matches the intended modernization brand, supports a credible Laravel demonstration, and provides a reproducible local environment without host PHP or MySQL dependencies.

## D002: Local-First Until Authorized

**Decision:** Complete and verify work locally before configuring GitHub or deployment.

**Reason:** The user will provide credentials later and explicitly requested that no credentials or remote actions be taken independently.

## D003: Demonstration Ledger

**Decision:** Use a clearly labeled seeded billing scenario for the current Revenue Integrity module.

**Reason:** It supports repeatable demonstrations and automated testing. It must not be described as a live billing-provider integration.

## D004: Service-First Positioning

**Decision:** Market Legacy Laravel Rescue as an expert service powered by Rescue Lab, not as a generic CRUD SaaS application.

**Reason:** The primary value is the combination of business-risk diagnosis, rescue expertise, modernization execution, and verified outcomes.

## D005: Preserve Revenue Integrity

**Decision:** Keep the existing Revenue Integrity module and add Legacy Assessment functionality alongside it.

**Reason:** Revenue Integrity is a useful business-outcome demonstration and a vertical use case for legacy modernization. Replacing it would discard tested work and reduce the story's business relevance.

## D006: TMPL Owns Public Marketing

**Decision:** The public Legacy Laravel Rescue page should live on the existing TMPL marketing website when that repository and stack are provided.

**Reason:** TMPL is the public brand. Rescue Lab should be deployed as the application or demonstration environment, potentially on a dedicated subdomain.

## D007: No Production Buildpack Yet

**Decision:** Do not create a platform-specific production buildpack until the hosting target is selected.

**Reason:** Laravel Cloud can build Laravel directly, while generic container hosts need a production Dockerfile. Creating both before choosing a target adds unnecessary maintenance.

## D008: Narrow Legacy Assessment MVP

**Decision:** Build a small number of evidence-backed scanner rules and one verified modernization example before adding broad automation.

**Reason:** Technical prospects will value evidence and trust more than the number of checks. A shallow scanner would reinforce the perception that the product is only CRUD.

## D009: Honest Recovery Language

**Decision:** A recovered incident represents an operationally recorded outcome, not proof that cash moved or a billing provider was updated.

**Reason:** The current system has no payment-provider write integration. Prospect language must remain accurate.

## D010: Recommended Deployment Shape

**Decision:** Keep source code in a private Git repository, deploy Rescue Lab on a Laravel-capable platform with managed MySQL, and link to it from the TMPL website.

**Status:** Recommended but not yet authorized or implemented.
