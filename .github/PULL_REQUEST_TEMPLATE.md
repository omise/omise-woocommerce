#### 1. Objective

Explain in non-technical terms **WHY this PR is required**.
E.g.: What feature it adds, what problem it solves...

This section will be used in the release notes. 

**Related information**:
Related issue(s): #< GitHub ticket number > (optional)

#### 2. Description of change

A general description of **WHAT changed in the codebase**, but short of an English version of the diff. Assume that people reading this will also be looking at the output of `git diff` and guide them to the highlights.

Additionally add the reasoning for change details if they're complex or abstract.

#### 3. Quality assurance

Specify where and how you tested this and what further testing it might need.

**🔧 Environments:**

Specify the details of your test environments, including, for each, the platform version (on which the plugin was run), the Omise plugin version, and the versions of your system software such as PHP or Ruby.

i.e.
- **WooCommerce**: v4.3.0
- **WordPress**: v5.4.2
- **PHP version**: 7.3.3
- **Omise plugin version**: Omise-WooCommerce 4.3 (optional, in case of submitting a new issue)

**✏️ Details:**

Explain how to manually test this feature.
For example if changes were made in the UI or in the API, explain where and if any specific access is needed.

#### 4. Impact of the change

List the steps that must be taken for this PR to work.
E.g.: rake yak:shave, Add "yak_key" to environment variables, ...

Be sure to include all systems that needs to be changed or which system is affected by the change
(Ex: Requires Elastic search to be installed and configured in secrets.yml).

Note: Please provide a screenshot if your changed impact to UI.

#### 5. Priority of change

Normal, High or Immediate.

#### 6. Additional Notes

Any further information that you would like to add.