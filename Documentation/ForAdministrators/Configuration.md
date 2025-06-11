# Configuration

## Site configuration

Configuration have to be set in the TYPO3 site config.
The following configuration options are available:

```yaml
settings:
  eventSubmission:
    storagePageUid: <page uid>
```

### eventSubmission (array)

Contains all configuration options.

**Path**  Site `settings.eventSubmission`

### storagePageUid (int)

Page id where the job records are to be stored.

**Path:**  Site `settings.eventSubmission.storagePageUid`
**Default:**  `9`
