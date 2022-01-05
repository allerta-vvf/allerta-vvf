//based on https://stackoverflow.com/a/42199863

const { writeFileSync } = require('fs');
const { promisify } = require('util');
const { exec } = require('child_process');
const exec_promise = promisify(exec);

async function createVersionsFile(filename) {
  const revision = (await exec_promise('git rev-parse --short HEAD')).stdout.toString().trim();
  const revision_timestamp = parseInt((await exec_promise('git log -1 --format="%at"')).stdout.toString().trim())*1000;
  const branch = (await exec_promise('git rev-parse --abbrev-ref HEAD')).stdout.toString().trim();
  const remote_url = (await exec_promise('git config --get remote.origin.url')).stdout.toString().trim();

  console.log(`revision: '${revision}', revision_timestamp: '${revision_timestamp}', branch: '${branch}', remote_url: '${remote_url}'`);

  const content = 
`// this file is automatically generated by git.version.js script
export const versions = {
  revision: '${revision}',
  revision_timestamp: ${revision_timestamp},
  branch: '${branch}',
  remote_url: '${remote_url}'
};`;

  writeFileSync(filename, content, {encoding: 'utf8'});
}

createVersionsFile('src/environments/versions.ts');