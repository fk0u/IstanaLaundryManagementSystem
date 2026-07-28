# Phase: Security Hardening + Caching

> Started: 2026-07-28  
> Branch: **`chore/security-and-caching`**  
> Epic: [#14](https://github.com/fk0u/IstanaLaundryManagementSystem/issues/14) · Linear [KIL-22](https://linear.app/kiloux/issue/KIL-22)

## Workflow

```bash
git fetch origin
git checkout chore/security-and-caching
git pull origin chore/security-and-caching
```

Scope = **non-functional only** (security + cache/queue). No large business features.

## Issue map

| Priority | GH | Linear | Title |
|----------|-----|--------|--------|
| Epic | [#14](https://github.com/fk0u/IstanaLaundryManagementSystem/issues/14) | KIL-22 | Parent tracking |
| P0 | [#15](https://github.com/fk0u/IstanaLaundryManagementSystem/issues/15) | KIL-23 | Role middleware |
| P0 | [#16](https://github.com/fk0u/IstanaLaundryManagementSystem/issues/16) | KIL-24 | Auth register / API throttle |
| P0 | [#17](https://github.com/fk0u/IstanaLaundryManagementSystem/issues/17) | KIL-25 | Tenant /track + BranchScoped |
| P0 | [#18](https://github.com/fk0u/IstanaLaundryManagementSystem/issues/18) | KIL-26 | Audit log mutations |
| P1 | [#19](https://github.com/fk0u/IstanaLaundryManagementSystem/issues/19) | KIL-27 | Journal lock + idempotency |
| P1 | [#20](https://github.com/fk0u/IstanaLaundryManagementSystem/issues/20) | KIL-28 | Docker/Nginx hygiene |
| Perf | [#21](https://github.com/fk0u/IstanaLaundryManagementSystem/issues/21) | KIL-29 | Caching + queue |

Order: **#15 → #16 → #17 → #18 → #19 → #20 → #21**

Prompts: [AI_PROMPTS.md](AI_PROMPTS.md)
