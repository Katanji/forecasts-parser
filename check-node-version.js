import semver from 'semver';
import { readFile } from 'fs/promises';

const packageJson = JSON.parse(
    await readFile(new URL('./package.json', import.meta.url))
);

const version = packageJson.engines.node;
if (!semver.satisfies(process.version, version)) {
    console.error(`Required node version ${version} not satisfied with current version ${process.version}.`);
    process.exit(1);
}
